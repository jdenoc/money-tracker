### Feature: POST /api/entries
### Feature: POST /api/entries/{page}

**Scenario:** attempt to retrieve a list of entries via the POST request when no entries exist
> **GIVEN:** no entries exist  
> **AND:** a series of filter combinations are available for sending via POST body  
> **WHEN:** sending a POST request to /api/entries  
> **AND:** a POST body is provided with JSON containing filter combinations  
> **THEN:** receive a 404 status  
> **AND:** a response with JSON that denotes an empty array  

_example output:_
```json
[]
```

- - -

**Scenario:** attempt to retrieve a list of entries via a POST request given certain filters
> **GIVEN:** 50 or less entries exist  
> **AND:** some of those entries are marked as deleted  
> **AND:** a series of filter combinations are available for sending via POST body  
> **AND:** an account_type associated with the entries exists  
> **AND:** an account associated with the account_type exists  
> **AND:** tags exist  
> **WHEN:** sending a POST request to /api/entries  
> **AND:** a POST body is provided with JSON containing filter combinations  
> **THEN:** receive a 200 status  
> **AND:** a response with json containing a list of up to 50 non-deleted entries that match the provided filter combinations  
> **AND:** within the json is a "count" node that contains the total number of non-deleted entries that match the provided filter combinations  
> **AND:** entries nodes within json contain entry details including "has_attachments" and "tags"  
> **AND:** entry tags node is an array  
> **AND:** entry has_attachments node is a boolean  

_example output:_
```json
{
  "0": {
    "id": 1,
    "entry_date": "1970-01-01",
    "entry_value": "0.01",
    "memo": "test entry 1",
    "expense": 1,
    "confirm": 0,
    "create_stamp": "1970-01-01 00:00:00",
    "modified_stamp": "1970-01-01 00:00:00",
    "has_attachments": true,
    "account_type": 2,
    "tags": [1, 2, 3]
  },
  "1": {
    "id": 2,
    "entry_date": "1970-01-01",
    "entry_value": "0.01",
    "memo": "test entry 2",
    "expense": 0,
    "confirm": 0,
    "create_stamp": "1970-01-01 00:00:00",
    "modified_stamp": "1970-01-01 00:00:00",
    "has_attachments": false,
    "account_type": 1,
    "tags": [1, 2]
  },
  "3": {
    "id": 2,
    "entry_date": "1970-01-01",
    "entry_value": "0.01",
    "memo": "test entry 3",
    "expense": 0,
    "confirm": 0,
    "create_stamp": "1970-01-01 00:00:00",
    "modified_stamp": "1970-01-01 00:00:00",
    "has_attachments": true,
    "account_type": 2,
    "tags": []
  },
  "5": {
    "id": 2,
    "entry_date": "1970-01-01",
    "entry_value": "0.01",
    "memo": "test entry 4",
    "expense": 0,
    "confirm": 0,
    "create_stamp": "1970-01-01 00:00:00",
    "modified_stamp": "1970-01-01 00:00:00",
    "has_attachments": false,
    "account_type": 1,
    "tags": []
  },
  "count": 4
}
```

- - -

**Scenario:** attempt to retrieve successive lists of entries via a POST request given certain filters
> **GIVEN:** between 101 and 150 entries exist  
> **AND:** for simplicities sake, none are marked deleted  
> **AND:** a series of filter combinations are available for sending via POST body  
> **AND:** an account_type associated with the entries exists  
> **AND:** an account associated with the account_type exists  
> **AND:** tags exist  
> **WHEN:** sending a POST request to /api/entries/0  
> **AND:** send a POST request to /api/entries/1  
> **AND:** send a POST request to /api/entries/2  
> **AND:** a POST body is provided to each POST request with JSON containing filter combinations  
> **THEN:** receive a 200 status from each POST request  
> **AND:** a response from each request that contains json containing a list of up to 50 entries that match the provided filter combinations  
> **AND:** within the json of each response is a "count" node that contains the total number of entries that match the provided filter combinations  
> **AND:** entries nodes within json contain entry details including "has_attachments" and "tags"  
> **AND:** entry tags node is an array  
> **AND:** entry has_attachments node is a boolean  
      
_POST /api/entries/0 example output:_
```
{
 "0": {
    "id": 1,
    "entry_date": "1970-01-01",
    "entry_value": "0.01",
    "memo": "test entry 1",
    "expense": 1,
    "confirm": 0,
    "create_stamp": "1970-01-01 00:00:00",
    "modified_stamp": "1970-01-01 00:00:00",
    "has_attachments": true,
    "account_type": 2,
    "tags": []
  },
  "1": {
    "id": 2,
    "entry_date": "1970-01-01",
    "entry_value": "0.01",
    "memo": "test entry 2",
    "expense": 0,
    "confirm": 0,
    "create_stamp": "1970-01-01 00:00:00",
    "modified_stamp": "1970-01-01 00:00:00",
    "has_attachments": false,
    "account_type": 1,
    "tags": [1, 3]
  },
  ...
  "49": {
    "id": 50,
    "entry_date": "1970-01-01",
    "entry_value": "0.01",
    "memo": "test entry 50",
    "expense": 0,
    "confirm": 0,
    "create_stamp": "1970-01-01 00:00:00",
    "modified_stamp": "1970-01-01 00:00:00",
    "has_attachments": true,
    "account_type": 1,
    "tags": [1, 3]
  },
  "count": 127
}
```

_POST /api/entries/1 example output:_
```
{
 "50": {
    "id": 51,
    "entry_date": "1970-01-01",
    "entry_value": "0.01",
    "memo": "test entry 51",
    "expense": 1,
    "confirm": 0,
    "create_stamp": "1970-01-01 00:00:00",
    "modified_stamp": "1970-01-01 00:00:00",
    "has_attachments": true,
    "account_type": 2,
    "tags": []
  },
  "51": {
    "id": 52,
    "entry_date": "1970-01-01",
    "entry_value": "0.01",
    "memo": "test entry 52",
    "expense": 0,
    "confirm": 0,
    "create_stamp": "1970-01-01 00:00:00",
    "modified_stamp": "1970-01-01 00:00:00",
    "has_attachments": false,
    "account_type": 1,
    "tags": [1, 3]
  },
  ...
  "99": {
    "id": 100,
    "entry_date": "1970-01-01",
    "entry_value": "0.01",
    "memo": "test entry 100",
    "expense": 0,
    "confirm": 0,
    "create_stamp": "1970-01-01 00:00:00",
    "modified_stamp": "1970-01-01 00:00:00",
    "has_attachments": true,
    "account_type": 1,
    "tags": [1, 3]
  },
  "count": 127
}
```

_POST /api/entries/2 example output:_
```
{
 "100": {
    "id": 101,
    "entry_date": "1970-01-01",
    "entry_value": "0.01",
    "memo": "test entry 101",
    "expense": 1,
    "confirm": 0,
    "create_stamp": "1970-01-01 00:00:00",
    "modified_stamp": "1970-01-01 00:00:00",
    "has_attachments": true,
    "account_type": 2,
    "tags": []
  },
  "101": {
    "id": 102,
    "entry_date": "1970-01-01",
    "entry_value": "0.01",
    "memo": "test entry 102",
    "expense": 0,
    "confirm": 0,
    "create_stamp": "1970-01-01 00:00:00",
    "modified_stamp": "1970-01-01 00:00:00",
    "has_attachments": false,
    "account_type": 1,
    "tags": [1, 3]
  },
  ...
  "126": {
    "id": 127,
    "entry_date": "1970-01-01",
    "entry_value": "0.01",
    "memo": "test entry 127",
    "expense": 0,
    "confirm": 0,
    "create_stamp": "1970-01-01 00:00:00",
    "modified_stamp": "1970-01-01 00:00:00",
    "has_attachments": true,
    "account_type": 1,
    "tags": [1, 3]
  },
  "count": 127
}
```

- - -

**Scenario:** attempt to retrieve a list of entries when `start_date` filter is greater than `end_date` filter
> **GIVEN:** between 4 and 50 entries exist  
> **AND:** for simplicities sake, none are marked deleted  
> **AND:** an account_type associated with the entries exists  
> **AND:** an account associated with the account_type exists  
> **AND:** there is a filter combination containing `start_date` and `end_date`, where `start_date` is greater than `end_date`  
> **WHEN:** sending a POST request to /api/entries  
> **AND:** a POST body is provided with JSON containing filter combinations  
> **THEN:** receive a 404 status  
> **AND:** a response with JSON that denotes an empty array

_example output:_
```json
[]
```

- - -

**Scenario:** attempt to retrieve a list of entries when `entry_value_min` filter is greater than `entry_value_max` filter
> **GIVEN:** between 4 and 50 entries exist  
> **AND:** for simplicities sake, none are marked deleted  
> **AND:** an account_type associated with the entries exists  
> **AND:** an account associated with the account_type exists  
> **AND:** there is a filter combination containing `entry_value_min` and `entry_value_max`, where `entry_value_min` is greater than `entry_value_max`  
> **WHEN:** sending a POST request to /api/entries  
> **AND:** a POST body is provided with JSON containing filter combinations  
> **THEN:** receive a 404 status  
> **AND:** a response with JSON that denotes an empty array

_example output:_
```json
[]
```