### Feature: PUT /api/entry/{entry_id}

**Scenario:** attempt to update an entry record without passing any data
> **GIVEN:** you wish to update an entry  
> **AND:** you have an existing entry  
> **AND:** you an entry ID  
> **AND:** you have no data  
> **WHEN:** sending a PUT request to /api/entry/{entry_id}  
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

**Scenario:** attempt to update an entry record and the new account_type to be associated does not exist
> **GIVEN:** you wish to update an entry  
> **AND:** you have an existing entry  
> **AND:** you have an entry ID  
> **AND:** you have entry data that includes a new account_type value  
> **AND:** account_type does not exist  
> **WHEN:** sending a PUT request to /api/entry  
> **AND:** you send the data as json in the put body  
> **THEN:** receive a 400 status from POST request  
> **AND:** receive a json response  
> **AND:** response contains a non-empty error message  
> **AND:** response contains an entry id of 0

example output:
```json
{"id":0, "error":"account_type does not exist"}
```

- - -

**Scenario:** attempt to update an entry record but entry does not exist
> **GIVEN:** you wish to update an entry  
> **AND:** the entry does not exist  
> **WHEN:** sending a PUT request to /api/entry/{entry_id}  
> **AND:** there is no put body content  
> **THEN:** receive a 404 status  
> **AND:** receive a json response  
> **AND:** response contains a non-empty error message  
> **AND:** response contains an entry id of 0

example output:
```json
{"error":"entry does not exist", "id":0}
```

- - -

**Scenario:** attempt to update an existing entry record and confirm account total has been updated
> **GIVEN:** you wish to update an entry  
> **AND:** you have an existing entry  
> **AND:** you have an entry ID  
> **AND:** you have entry data containing a new entry value  
> **AND:** an account_type associated with the existing entry  
> **AND:** an account associated with the account_type  
> **AND:** there are no attachments associated with the entry  
> **WHEN:** sending a GET request to /api/account/{account_id} to get account total  
> **AND:** send a PUT request to /api/entry/{entry_id}  
> **AND:** you send the data as json in the put body  
> **AND:** send a GET request to /api/entry/{entry_id} to confirm entry created correctly  
> **AND:** send a GET request to /api/accounts to confirm total was updated  
> **THEN:** receive a 200 status from the first GET /api/account/{account_id} request  
> **AND:** receive a json response containing account data (see [GET /api/account/{account_id}](GET_API_ACCOUNT_ACCOUNTID.md]))  
> **AND:** receive a 200 status from the PUT request  
> **AND:** receive a json response from PUT request  
> **AND:** PUT response contains the ID of the updated entry  
> **AND:** PUT response contains an empty error message  
> **AND:** receive a 200 status from the GET /api/entry/entry{entry_id} request  
> **AND:** receive a json response containing entry data (see [GET /api/entry/{entry_id}](GET_API_ENTRY_ENTRYID.md))  
> **AND:** confirm entry value has changed  
> **AND:** receive a 200 status from the second GET /api/account/{account_id} request  
> **AND:** receive a json response with entry data from the GET request (see [GET /api/account/{account_id}](GET_API_ACCOUNT_ACCOUNTID.md]))  
> **AND:** confirm account total was updated correctly

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
            "type_name": "account 1 debit card",
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
  "account_type": 3,
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
            "type_name": "account 1 debit card",
            "last_digits": "1234"
        }
    ]
}
```

- - -

**Scenario:** attempt to mark an existing entry as deleted and confirm value is no longer included in account total
> **GIVEN:** we want to update and existing entry  
> **AND:** an entry exists  
> **AND:** we have entry data, `deleted=1`  
> **AND:** we have an account_type associated with the entry  
> **AND:** we and an account associated with the account_type  
> **WHEN:** sending a GET request to /api/account/{account_id} to get the original account total  
> **AND:** send a PUT request to /api/entry/{entry_id}  
> **AND:** the PUT request contains json data  
> **AND:** send a GET request to /api/entry/{entry_id}  
> **AND:** send a GET request to /api/account/{account_id}  
> **THEN:** receive a 200 status from the first GET /api/account/{account_id} request  
> **AND:** receive a json response containing account data (see [GET /api/account/{account_id}](GET_API_ACCOUNT_ACCOUNTID.md]))  
> **AND:** receive a 200 status from PUT request  
> **AND:** receive a json response from PUT request  
> **AND:** PUT response contains the ID of the updated entry  
> **AND:** PUT response contains an empty error message  
> **AND:** receive a 404 status from GET entry request  
> **AND:** receive an empty json response  
> **AND:** receive a 200 status from the second GET /api/account/{account_id} request  
> **AND:** receive a json response with entry data from the GET request (see [GET /api/account/{account_id}](GET_API_ACCOUNT_ACCOUNTID.md]))  
> **AND:** confirm account total was updated correctly, i.e. entry value was removed from account total

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
            "type_name": "account 1 debit card",
            "last_digits": "1234"
        }
    ]
}
```
```json
{"id":5, "error":""}
```
```json
[]
```
```json
{
    "id": 1,
    "account": "test account 1",
    "total": "10.01",
    "account_types": [
        {
            "id": 3,
            "type": "debit card",
            "type_name": "account 1 debit card",
            "last_digits": "1234"
        }
    ]
}
```
- - -

**Scenario:** attempt to update entry with data, one property at a time with a batch of properties at the end. Excluding `deleted` and `entry_value`
> **GIVEN:** you wish to update an entry  
> **AND:** you have an existing entry  
> **AND:** you have an entry ID  
> **AND:** an account_type associated with the existing entry  
> **AND:** an account associated with the account_type  
> **AND:** you have entry data of one entry property  
> **AND:** sometimes entry data containing multiple entry properties  
> **WHEN:** sending a PUT request to /api/entry/{entry_id}  
> **AND:** send entry data with PUT request  
> **AND:** send a GET request to /api/entry/{entry_id} to confirm entry property updated  
> **THEN:** receive a 200 status from the PUT request  
> **AND:** PUT response contains json  
> **AND:** PUT response contains the ID of the updated entry  
> **AND:** PUT response contains an empty error message  
> **AND:** receive a 200 status from the GET request  
> **AND:** the response contains json (see [GET /api/account/{account_id}](GET_API_ACCOUNT_ACCOUNTID.md]))

_example output:_
```json
{"id":7, "error":""}
```
```json
{
  "id": 7,
  "entry_date": "1970-01-01",
  "entry_value": "0.01",
  "memo": "entry test",
  "expense": 1,
  "confirm": 0,
  "account_type": 3,
  "tags": [],
  "attachments": [],
  "create_stamp": "1970-01-01T00:00:00+00:00",
  "modified_stamp": "1970-01-01T00:00:01+00:00"
}
```

- - -

**Scenario:** attempt to update an entry record and a tag does not exist
> **GIVEN:** you wish to update an entry  
> **AND:** you have an existing entry  
> **AND:** you have an entry ID  
> **AND:** an account_type associated with the existing entry  
> **AND:** an account associated with the account_type  
> **AND:** you have entry data  
> **AND:** there are tags  
> **AND:** there is a tag associated with the entry data that does not exist  
> **AND:** there are no attachments associated with the entry  
> **WHEN:** sending a PUT request to /api/entry/{entry_id}  
> **AND:** you send the data as json in the put body  
> **AND:** send a GET request to /api/entry/{entry_id} to confirm entry created correctly  
> **THEN:** receive a 200 status from PUT request  
> **AND:** receive json from PUT response  
> **AND:** PUT response contains the ID of the created entry  
> **AND:** PUT response contains an empty error message  
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
  "account_type": 3,
  "tags": [
    1,
    2,
    3
  ],
  "attachments": [],
  "create_stamp": "1970-01-01T00:00:00+00:00",
  "modified_stamp": "1970-01-01T00:00:01+00:00"
}
```

- - -

**Scenario:** attempt to update an entry record with attachments
> **GIVEN:** you wish to update an entry  
> **AND:** an entry exists  
> **AND:** an entry ID  
> **AND:** an account_type associated with the existing entry  
> **AND:** an account associated with the account_type  
> **AND:** you have entry data  
> **AND:** data contains attachments associated with entry  
> **WHEN:** sending a PUT request to /api/entry/{entry_id}  
> **AND:** you send the data as json in the put body  
> **AND:** send a GET request to /api/entry/{entry_id} to confirm entry created correctly  
> **THEN:** receive a 200 status from PUT request  
> **AND:** PUT response contains the ID of the created entry  
> **AND:** PUT response contains an empty error message  
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
  "account_type": 1,
  "tags": [],
  "attachments": [
    {"attachment": "test1.txt", "uuid": "baa9302e-6ebc-437b-b5c7-32075e7a3ddd"},
    {"attachment": "test1.txt", "uuid": "a4c5286d-6b31-4ed3-8f3f-9a8e17e753f3"}
  ],
  "create_stamp": "1970-01-01T00:00:00+00:00",
  "modified_stamp": "1970-01-01T00:00:01+00:00"
}
```

**Scenario:** attempt to update an entry record without changing anything
> **GIVEN:** you wish to update an entry  
> **AND:** an entry exists  
> **AND:** an entry ID  
> **AND:** an account_type associated with the existing entry  
> **AND:** an account associated with the account_type  
> **AND:** you have entry data that is no different to the existing entry  
> **WHEN:** sending a PUT request to /api/entry/{entry_id}  
> **AND:** you send the data as json in the put body  
> **AND:** send a GET request to /api/entry/{entry_id} to confirm entry created correctly  
> **THEN:** receive a 200 status from PUT request  
> **AND:** PUT response contains the ID of the created entry  
> **AND:** PUT response contains an empty error message  
> **AND:** receive a 200 status from the GET request  
> **AND:** receive a json response with entry data from the GET request (see [GET /api/entry/{entry_id}](GET_API_ENTRY_ENTRYID.md))  
> **AND:** GET response contains an attachments node with attachments that exists  

example output:
```json
{"id":7, "error":""}
```
```json
{
  "id": 7,
  "entry_date": "1970-01-01",
  "entry_value": "0.01",
  "memo": "entry test",
  "expense": 1,
  "confirm": 0,
  "account_type": 1,
  "tags": [],
  "attachments": [],
  "create_stamp": "1970-01-01T00:00:00+00:00",
  "modified_stamp": "1970-01-01T00:00:01+00:00"
}
```