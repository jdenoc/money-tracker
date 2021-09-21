### Feature: POST /api/entry

**Scenario:** attempt to create a new entry record without passing any data
> **GIVEN:** you wish to create a new entry  
> **AND:** you have no data  
> **WHEN:** sending a POST request to /api/entry  
> **AND:** there is no post body content  
> **THEN:** receive a 400 status  
> **AND:** receive a json response  
> **AND:** response contains a non-empty error message  
> **AND:** response contains an entry id of 0

example output:
```json
{"error":"No data provided", "id":0}
```

- - -

**Scenario:** attempt to create a new entry record with some missing data that is _required_
> **GIVEN:** you wish to create a new entry  
> **AND:** you have data, but it is missing some required fields  
> **WHEN:** sending a POST request to /api/entry  
> **AND:** you send the incomplete data as json in the post body  
> **THEN:** receive a 400 status  
> **AND:** receive a json response  
> **AND:** response contains a non-empty error message  
> **AND:** response contains an entry id of 0

example output:
```json
{"error":"Missing data: <missing data>", "id":0}
```

- - -

**Scenario:** attempt to create a new entry record and the account_type associated does not exist
> **GIVEN:** you wish to create a new entry  
> **AND:** you have entry data  
> **AND:** account_type does not exist  
> **WHEN:** sending a POST request to /api/entry  
> **AND:** you send the data as json in the post body  
> **THEN:** receive a 400 status from POST request  
> **AND:** receive a json response  
> **AND:** response contains a non-empty error message  
> **AND:** response contains an entry id of 0

example output:
```json
{"id":0, "error":"account_type does not exist"}
```

- - -

**Scenario:** attempt to create a new entry record and confirm account total has been updated
> **GIVEN:** you wish to create a new entry  
> **AND:** you have data  
> **AND:** an account_type associated with the new entry exists  
> **AND:** an account associated with the account_type  
> **AND:** there are no attachments associated with the entry  
> **WHEN:** sending a GET request to /api/account/{account_id}  
> **AND:** send a POST request to /api/entry  
> **AND:** you send the data as json in the post body  
> **AND:** send a GET request to /api/entry/{entry_id} to confirm entry created correctly  
> **AND:** send a GET request to /api/accounts to confirm total was updated  
> **THEN:** receive a 200 status from the first GET /api/account/{account_id} request  
> **AND:** receive a json response containing account data (see [GET /api/account/{account_id}](GET_API_ACCOUNT_ACCOUNTID.md]))  
> **AND:** receive a 201 status from the POST request  
> **AND:** receive a json response from POST request  
> **AND:** POST response contains the ID of the created entry  
> **AND:** POST response contains an empty error message  
> **AND:** receive a 200 status from the GET /api/entry/entry{entry_id} request  
> **AND:** receive a json response containing entry data (see [GET /api/entry/{entry_id}](GET_API_ENTRY_ENTRYID.md))  
> **AND:** receive a 200 status from the second GET /api/account/{account_id} request  
> **AND:** receive a json response with entry data from the GET request (see [GET /api/account/{account_id}](GET_API_ACCOUNT_ACCOUNTID.md]))  

example output:
```json
{
    "id": 1,
    "account": "test account 1",
    "total": "10.00",
    "account_types": [
        {
            "id": 3,
            "type": "debit card",
            "name": "account 1 debit card",
            "last_digits": "1234"
        }
    ]
}
```
```json
{"id":5, "error":""}
```
```json
{
  "id": 5,
  "entry_date": "1970-01-01",
  "entry_value": "0.01",
  "memo": "entry test",
  "expense": 1,
  "confirm": 0,
  "account_type_id": 3,
  "tags": [],
  "attachments": [],
  "create_stamp": "1970-01-01T00:00:00+00:00",
  "modified_stamp": "1970-01-01T00:00:01+00:00"
}
```
```json
{
    "id": 1,
    "account": "test account 1",
    "total": "9.99",
    "account_types": [
        {
            "id": 3,
            "type": "debit card",
            "name": "account 1 debit card",
            "last_digits": "1234"
        }
    ]
}
```

- - -

**Scenario:** attempt to create a new entry record and a tag does not exist
> **GIVEN:** you wish to create a new entry  
> **AND:** an account_type associated with the new entry exists  
> **AND:** an account associated with the account_type  
> **AND:** you have entry data  
> **AND:** there are tags  
> **AND:** there is a tag associated with the entry data that does not exist  
> **AND:** there are no attachments associated with the entry  
> **WHEN:** sending a POST request to /api/entry  
> **AND:** you send the data as json in the post body  
> **AND:** send a GET request to /api/entry/{entry_id} to confirm entry created correctly  
> **THEN:** receive a 201 status from POST request  
> **AND:** receive json from POST response  
> **AND:** POST response contains the ID of the created entry  
> **AND:** POST response contains an empty error message  
> **AND:** receive a 200 status from the GET /api/entry/entry{entry_id} request  
> **AND:** receive a json response containing entry data (see [GET /api/entry/{entry_id}](GET_API_ENTRY_ENTRYID.md))  
> **AND:** GET response contains a tags node with tag IDs that exists  
> **AND:** GET response tag node does not contain tag ID that does not exist  

example output:
```json
{"id":4, "error":""}
```
```json
{
  "id": 4,
  "entry_date": "1970-01-01",
  "entry_value": "0.01",
  "memo": "entry test",
  "expense": 1,
  "confirm": 0,
  "account_type_id": 3,
  "tags": [1, 2, 3],
  "attachments": [],
  "create_stamp": "1970-01-01T00:00:00+00:00",
  "modified_stamp": "1970-01-01T00:00:01+00:00"
}
```

- - -

**Scenario:** attempt to create a new entry record with attachments
> **GIVEN:** you wish to create a new entry  
> **AND:** an account_type associated with the new entry exists  
> **AND:** an account associated with the account_type  
> **AND:** you have entry data  
> **AND:** data contains attachments associated with entry  
> **WHEN:** sending a POST request to /api/entry  
> **AND:** you send the data as json in the post body  
> **AND:** send a GET request to /api/entry/{entry_id} to confirm entry created correctly  
> **THEN:** receive a 201 status from POST request  
> **AND:** POST response contains the ID of the created entry  
> **AND:** POST response contains an empty error message  
> **AND:** receive a 200 status from the GET request  
> **AND:** receive a json response with entry data from the GET request (see [GET /api/entry/{entry_id}](GET_API_ENTRY_ENTRYID.md))  
> **AND:** GET response contains an attachments node with attachments that exists  

example output:
```json
{"id":6, "error":""}
```
```json
{
  "id": 6,
  "entry_date": "1970-01-01",
  "entry_value": "0.01",
  "memo": "entry test",
  "expense": 1,
  "confirm": 0,
  "account_type_id": 1,
  "tags": [],
  "attachments": [
    {"attachment": "test1.txt", "uuid": "baa9302e-6ebc-437b-b5c7-32075e7a3ddd"},
    {"attachment": "test1.txt", "uuid": "a4c5286d-6b31-4ed3-8f3f-9a8e17e753f3"}
  ],
  "create_stamp": "1970-01-01T00:00:00+00:00",
  "modified_stamp": "1970-01-01T00:00:01+00:00"
}
```