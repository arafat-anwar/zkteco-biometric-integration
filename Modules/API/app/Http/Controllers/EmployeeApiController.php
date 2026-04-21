<?php

namespace Modules\API\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Authentication\Models\Organization;
use Modules\Credentials\Models\Employee;

/**
 * @group Employees
 */
class EmployeeApiController extends Controller
{
    /**
     * List Employees
     *
     * Get a paginated list of employees. Managers only.
     *
     * @authenticated
     * @queryParam organization_id integer Filter by organization. Example: 1
     * @queryParam search string Search by name, badge, or card number. Example: John
     * @queryParam per_page integer Results per page (max 100). Example: 30
     *
     * @response 200 {
     *   "success": true,
     *   "data": [],
     *   "meta": {"total": 10, "per_page": 30, "current_page": 1}
     * }
     */
    public function index(Request $request)
    {
        $this->authorizeManager($request);

        $query = Employee::with("organization:id,name");

        if ($orgId = $request->get("organization_id")) {
            $query->where("organization_id", $orgId);
        }

        if ($search = $request->get("search")) {
            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")
                  ->orWhere("badge_number", "like", "%{$search}%")
                  ->orWhere("card_no", "like", "%{$search}%");
            });
        }

        $perPage   = min((int) $request->get("per_page", 30), 100);
        $employees = $query->orderBy("name")->paginate($perPage);

        return response()->json([
            "success" => true,
            "data"    => $employees->items(),
            "meta"    => [
                "total"        => $employees->total(),
                "per_page"     => $employees->perPage(),
                "current_page" => $employees->currentPage(),
                "last_page"    => $employees->lastPage(),
            ],
        ]);
    }

    /**
     * Create Employee
     *
     * Create a new employee record. Managers only.
     *
     * @authenticated
     * @bodyParam organization_id integer required Organization ID. Example: 1
     * @bodyParam mdb_user_id integer required Device user ID from MDB. Example: 1001
     * @bodyParam name string required Full name. Example: John Doe
     * @bodyParam badge_number string Badge/employee number. Example: EMP001
     * @bodyParam card_no string Card number. Example: CARD001
     * @bodyParam department_id integer Department ID. Example: 2
     * @bodyParam privilege integer Privilege level (0=Normal,1=Enroller,2=Manager,3=Admin). Example: 0
     * @bodyParam is_active boolean Active status. Example: true
     *
     * @response 201 {"success": true, "data": {}}
     * @response 422 {"success": false, "message": "Duplicate employee in this organization."}
     */
    public function store(Request $request)
    {
        $this->authorizeManager($request);

        $data = $request->validate([
            "organization_id" => ["required", "exists:organizations,id"],
            "mdb_user_id"     => ["required", "integer", "min:1"],
            "name"            => ["required", "string", "max:255"],
            "badge_number"    => ["nullable", "string", "max:50"],
            "card_no"         => ["nullable", "string", "max:100"],
            "department_id"   => ["nullable", "integer"],
            "privilege"       => ["integer", "in:0,1,2,3"],
            "is_active"       => ["boolean"],
        ]);

        if (Employee::where("organization_id", $data["organization_id"])
            ->where("mdb_user_id", $data["mdb_user_id"])->exists()) {
            return response()->json([
                "success" => false,
                "message" => "An employee with this User ID already exists in the selected organization.",
            ], 422);
        }

        $employee = Employee::create([
            ...$data,
            "privilege" => $data["privilege"] ?? 0,
            "is_active" => $request->boolean("is_active", true),
        ]);

        return response()->json(["success" => true, "data" => $employee->load("organization:id,name")], 201);
    }

    /**
     * Get Employee
     *
     * Get a single employee by ID. Managers only.
     *
     * @authenticated
     * @urlParam employee integer required Employee ID. Example: 1
     *
     * @response 200 {"success": true, "data": {}}
     */
    public function show(Employee $employee)
    {
        $this->authorizeManager(request());
        $employee->load("organization:id,name");

        return response()->json(["success" => true, "data" => $employee]);
    }

    /**
     * Update Employee
     *
     * Update an existing employee record. Managers only.
     *
     * @authenticated
     * @urlParam employee integer required Employee ID. Example: 1
     * @bodyParam organization_id integer required Organization ID. Example: 1
     * @bodyParam mdb_user_id integer required Device user ID. Example: 1001
     * @bodyParam name string required Full name. Example: John Doe
     * @bodyParam badge_number string Badge number. Example: EMP001
     * @bodyParam card_no string Card number. Example: CARD001
     * @bodyParam department_id integer Department ID. Example: 2
     * @bodyParam privilege integer Privilege level (0-3). Example: 0
     * @bodyParam is_active boolean Active status. Example: true
     *
     * @response 200 {"success": true, "data": {}}
     */
    public function update(Request $request, Employee $employee)
    {
        $this->authorizeManager($request);

        $data = $request->validate([
            "organization_id" => ["required", "exists:organizations,id"],
            "mdb_user_id"     => ["required", "integer", "min:1"],
            "name"            => ["required", "string", "max:255"],
            "badge_number"    => ["nullable", "string", "max:50"],
            "card_no"         => ["nullable", "string", "max:100"],
            "department_id"   => ["nullable", "integer"],
            "privilege"       => ["integer", "in:0,1,2,3"],
            "is_active"       => ["boolean"],
        ]);

        $duplicate = Employee::where("organization_id", $data["organization_id"])
            ->where("mdb_user_id", $data["mdb_user_id"])
            ->where("id", "!=", $employee->id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                "success" => false,
                "message" => "Another employee with this User ID already exists in the selected organization.",
            ], 422);
        }

        $employee->update([...$data, "is_active" => $request->boolean("is_active")]);

        return response()->json(["success" => true, "data" => $employee->fresh("organization:id,name")]);
    }

    /**
     * Delete Employee
     *
     * Delete an employee record. Managers only.
     *
     * @authenticated
     * @urlParam employee integer required Employee ID. Example: 1
     *
     * @response 200 {"success": true, "message": "Employee deleted."}
     */
    public function destroy(Employee $employee)
    {
        $this->authorizeManager(request());
        $employee->delete();

        return response()->json(["success" => true, "message" => "Employee deleted."]);
    }

    private function authorizeManager(Request $request): void
    {
        if (!$request->user()->isManager()) {
            abort(403, "Unauthorized");
        }
    }
}
