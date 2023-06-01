# sgn-rfid-api
Proof of concept RFID auth

## Requirements
1. PHP 8.1

## Installation
1. Clone the repository
2. Run `composer install` in order to get the package dependencies
3. Create a new MySQL Database
4. Rename `.env.example` to `.env` 
5. Set your database configuration in `.env`
6. Open a terminal in the project root and run `php sgn Install`. This will run the CLI installer, that will create the Schema and import the demo data
7. During the installation process, the employees will get randomly generated `md5` hash for their RFID card number

## Notes
1. Demo data is imported from a JSON file, which can be found in `example_data/demoData.json`
2. A dump of the current database (for reference) can be found in `example_data/dump.sql.gz`
3. Once ran, the `Intall` command will create a `install.lock` file, preventing you from running it again.
4. There are no endpoints for managing employees / buildings / departments, but there's logic inside, which can be easily used in order to create those if needed
5. There's an Apache vHost configuration file, which can be found in `example_data/local-api.conf` (file is just for reference)

## How it works
Authentication into a building with an RFID card:

1. Checks for a valid `employee`, based on the provided RFID card number. Exception if nothing is found.
2. If we found our employee, we're checking if we have any `departments`
 attached.
3. Check for a valid `building`, based on the provided `building_id` parameter. Exception if nothing has been found.
4. In case the employee is assigned to department(s) and the building has departments assigned as well, then the employee and the building must have at least one department in common. In case the employee is not assigned to any departments then he can freely enter a building without departments as well.

## Sample request

The only endpoint available in this API is the one, that will authenticate the employee with his RFID card number in the building.

cUrl code is taken from `Postman`

`http://local-api.com` is a local testing comain

```
curl --location 'http://local-api.com?action=building%2Fenter&building_id=5&rfid=2a73fa6c5694e9b91c3f258a9d01e174' \
--header 'Cookie: ci_sessions=hmtjqsp4a97me7p7n3a51frjc9jkifgu'
```

Response:

```
{
    "data": {
        "full_name": "Nayden Panchev",
        "departments": [
            {
                "department_name": "Development",
                "department_code": "development",
                "department_id": 2
            }
        ]
    }
}
```

### Request Parameters:
1. `action` => right now there's only one `action` allowed => `building/enter`. This will execute the `enter` method from the `BuildingController`.
2. `building_id` => a valid ID of the building we're trying to enter
3. `rfid` => the RFID card number, which represents a valid `employee` record

## Testing - currently there are no written test cases

## TODO
1. Better error handling - add separate exception classes with appropriate error codes (`500`, `422`, `409`, `404`)
2. Router
3. CRUD for buildings / departments / employees
4. Use FAKER for generating random employees | buildings | departments for demo data, rather than having them hardcoded in a .json file
