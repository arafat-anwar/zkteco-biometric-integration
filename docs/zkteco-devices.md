# ZKTeco Devices

## Table of contents

- [Supported workflows](#supported-workflows)
- [Notes](#notes)

ZKTeco devices typically accept network queries on TCP port `4370`. This project supports:

- Pulling users and logs via device protocol/SDK
- Export-to-MDB workflow (vendor tools) and subsequent import

Notes:

- Track `last_sync` per device to avoid duplicates.
- Implement retries and backoff for flaky networks.
