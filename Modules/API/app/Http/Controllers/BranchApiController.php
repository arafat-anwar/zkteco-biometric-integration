<?php

namespace Modules\API\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Credentials\Models\Branch;

/**
 * @group Branches
 */
class BranchApiController extends Controller
{
    /**
     * List Branches
     *
     * Get a paginated list of branches. Managers only.
     *
     * @authenticated
     * @queryParam organization_id integer Filter by organization. Example: 1
     * @queryParam search string Search by name or location. Example: HQ
     * @queryParam per_page integer Results per page (max 100). Example: 30
     *
     * @response 200 {
     *   "success": true,
     *   "data": [],
     *   "meta": {"total": 5, "per_page": 30, "current_page": 1}
     * }
     */
    public function index(Request $request)
    {
        $this->authorizeManager($request);

        $query = Branch::with("organization:id,name");

        if ($orgId = $request->get("organization_id")) {
            $query->where("organization_id", $orgId);
        }

        if ($search = $request->get("search")) {
            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")
                  ->orWhere("location", "like", "%{$search}%");
            });
        }

        $perPage  = min((int) $request->get("per_page", 30), 100);
        $branches = $query->orderBy("name")->paginate($perPage);

        return response()->json([
            "success" => true,
            "data"    => $branches->items(),
            "meta"    => [
                "total"        => $branches->total(),
                "per_page"     => $branches->perPage(),
                "current_page" => $branches->currentPage(),
                "last_page"    => $branches->lastPage(),
            ],
        ]);
    }

    /**
     * Create Branch
     *
     * Create a new branch. Managers only.
     *
     * @authenticated
     * @bodyParam organization_id integer required Organization ID. Example: 1
     * @bodyParam name string required Branch name. Example: Head Office
     * @bodyParam location string Location description. Example: Dhaka
     * @bodyParam is_active boolean Active status. Example: true
     *
     * @response 201 {"success": true, "data": {}}
     * @response 422 {"success": false, "message": "A branch with this name already exists."}
     */
    public function store(Request $request)
    {
        $this->authorizeManager($request);

        $data = $request->validate([
            "organization_id" => ["required", "exists:organizations,id"],
            "name"            => ["required", "string", "max:255"],
            "location"        => ["nullable", "string", "max:255"],
            "is_active"       => ["boolean"],
        ]);

        if (Branch::where("organization_id", $data["organization_id"])
            ->where("name", $data["name"])->exists()) {
            return response()->json([
                "success" => false,
                "message" => "A branch with this name already exists in the selected organization.",
            ], 422);
        }

        $branch = Branch::create([...$data, "is_active" => $request->boolean("is_active", true)]);

        return response()->json(["success" => true, "data" => $branch->load("organization:id,name")], 201);
    }

    /**
     * Get Branch
     *
     * Get a single branch by ID. Managers only.
     *
     * @authenticated
     * @urlParam branch integer required Branch ID. Example: 1
     *
     * @response 200 {"success": true, "data": {}}
     */
    public function show(Branch $branch)
    {
        $this->authorizeManager(request());
        $branch->load("organization:id,name");

        return response()->json(["success" => true, "data" => $branch]);
    }

    /**
     * Update Branch
     *
     * Update an existing branch. Managers only.
     *
     * @authenticated
     * @urlParam branch integer required Branch ID. Example: 1
     * @bodyParam organization_id integer required Organization ID. Example: 1
     * @bodyParam name string required Branch name. Example: Head Office
     * @bodyParam location string Location description. Example: Dhaka
     * @bodyParam is_active boolean Active status. Example: true
     *
     * @response 200 {"success": true, "data": {}}
     */
    public function update(Request $request, Branch $branch)
    {
        $this->authorizeManager($request);

        $data = $request->validate([
            "organization_id" => ["required", "exists:organizations,id"],
            "name"            => ["required", "string", "max:255"],
            "location"        => ["nullable", "string", "max:255"],
            "is_active"       => ["boolean"],
        ]);

        $duplicate = Branch::where("organization_id", $data["organization_id"])
            ->where("name", $data["name"])
            ->where("id", "!=", $branch->id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                "success" => false,
                "message" => "Another branch with this name already exists in the selected organization.",
            ], 422);
        }

        $branch->update([...$data, "is_active" => $request->boolean("is_active")]);

        return response()->json(["success" => true, "data" => $branch->fresh("organization:id,name")]);
    }

    /**
     * Delete Branch
     *
     * Delete a branch. Managers only.
     *
     * @authenticated
     * @urlParam branch integer required Branch ID. Example: 1
     *
     * @response 200 {"success": true, "message": "Branch deleted."}
     */
    public function destroy(Branch $branch)
    {
        $this->authorizeManager(request());
        $branch->delete();

        return response()->json(["success" => true, "message" => "Branch deleted."]);
    }

    private function authorizeManager(Request $request): void
    {
        if (!$request->user()->isManager()) {
            abort(403, "Unauthorized");
        }
    }
}
