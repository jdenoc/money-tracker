### Feature: GET _/api/account/{account_id}_

**Scenario:** get basic account data that includes account_type information
> **GIVEN:** an account ID  
> **AND:** there is a record in the accounts database table  
> **AND:** there are records in the account_types database table  
> **WHEN:** visiting GET /api/account/{account_id}  
> **THEN:** receive a 200 status  
> **AND:** get a json response that contains account data

_example output:_
 ```json
{
    "id": 1,
    "name": "test account 1",
    "institution_id": 1,
    "disabled": false,
    "total": "10.00",
    "account_types": [
        {
            "id": 1,
            "type": "debit card",
            "type_name": "account 1 debit card",
            "last_digits": "1234",
            "disabled": false
        }, {
            "id": 2,
            "type": "checking",
            "type_name": "account 1 checking",
            "last_digits": "4321",
            "disabled": false
        }
    ],
    "create_stamp": "1970-01-01T00:00:00+00:00",
    "modified_stamp": "1999-12-31T23:59:59+00:00",
    "disabled_stamp": null
}
```

- - -

**Scenario:** get basic account data that includes account_type information where one of the account_types is disabled
> **GIVEN:** an account ID  
> **AND:** there is a record in the accounts database table  
> **AND:** there are records in the account_types database table  
> **AND:** one of the account_types records is disabled  
> **WHEN:** visiting GET /api/account/{account_id}  
> **THEN:** receive a 200 status  
> **AND:** get a json response that contains account data  
> **AND:** the disabled account_types record does not appear in the output  

_example output:_
```json
{
    "id": 1,
    "name": "test account 1",
    "institution_id": 1,
    "disabled": false,
    "total": "10.00",
    "account_types": [
        {
            "id": 1,
            "type": "debit card",
            "type_name": "account 1 debit card",
            "last_digits": "1234",
            "disabled": false
        }, {
            "id": 2,
            "type": "checking",
            "type_name": "account 1 checking",
            "last_digits": "4321",
            "disabled": false
        }
    ],
    "create_stamp": "1970-01-01T00:00:00+00:00",
    "modified_stamp": "1999-12-31T23:59:59+00:00",
    "disabled_stamp": null
}
```

- - -

**Scenario:** get basic account data that includes account_type information but no account_types are associated with the account
> **GIVEN:** an account ID  
> **AND:** there is a record in the accounts database table  
> **AND:** there are NO records in the account_types database table  
> **WHEN:** visiting GET /api/account/{account_id}  
> **THEN:** receive a 200 status  
> **AND:** get a json response that contains account data  
> **AND:** the account_types component is empty  

_example output:_
```json
{
    "id": 1,
    "name": "test account 1",
    "institution_id": 1,
    "disabled": false,
    "total": "10.00",
    "account_types": [],
    "create_stamp": "1970-01-01T00:00:00+00:00",
    "modified_stamp": "1999-12-31T23:59:59+00:00",
    "disabled_stamp": null
}
```

- - -

**Scenario:** get basic account data that includes account_type information but only disabled account_types are associated with the account
> **GIVEN:** an account ID  
> **AND:** there is a record in the accounts database table  
> **AND:** there are ONLY "disabled" records in the account_types database table  
> **WHEN:** visiting GET /api/account/{account_id}  
> **THEN:** receive a 200 status  
> **AND:** get a json response that contains account data  
> **AND:** the account_types component is empty  

_example output:_
```json
{
    "id": 1,
    "name": "test account 1",
    "institution_id": 1,
    "disabled": false,
    "total": "10.00",
    "account_types": [],
    "create_stamp": "1970-01-01T00:00:01+00:00",
    "modified_stamp": "1999-12-31T23:59:59+00:00",
    "disabled_stamp": null
}
```

- - -

**Scenario:** get basic account data when no account data exists
> **GIVEN:** an account ID  
> **AND:** there isn't any account data in the accounts database table  
> **WHEN:** visiting GET /api/account/{account_id}  
> **THEN:** receive a 404 status  
> **AND:** get an empty array in json format

_example output:_
```json
[]
```