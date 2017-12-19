### Feature: GET /api/account-types

**Scenario:** attempt to retrieve a list of account_types when no account_types exist
> **GIVEN:** no account_types exist
> **WHEN:** sending a GET request to /api/account-types  
> **THEN:** receive a 404 status  
> **AND:** the response contains an empty json array

_example output:_
```json
[]
```

- - -

**Scenario:** attempt to retrieve a list of account_types
> **GIVEN:** there account_types in the database  
> **WHEN:** sending a GET request to /api/account-types   
> **THEN:** receive a 200 status  
> **AND:** response contains json listing all account_types (enabled & disabled)

_example output:_
```json
{
    "0":{
        "id":1,
        "type":"checking",
        "last_digits":"1234",
        "name":"type - checking",
        "account_id":1,
        "disabled":false
    },
    "1":{
        "id":2,
        "type":"debit card",
        "last_digits":"4321",
        "name":"type - debit card",
        "account_id":1,
        "disabled":true
    },
    "2":{
        "id":3,
        "type":"savings",
        "last_digits":"9876",
        "name":"type - savings",
        "account_id":2,
        "disabled":false
    }
}
```