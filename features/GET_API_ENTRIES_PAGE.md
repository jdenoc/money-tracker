### Feature: GET _/api/entries/{page}_

**Scenario:** get first page of a truncated list of available entries when there are more than 50 entries
> **GIVEN:** there are between 50 and 150 entries (for simplicity we won't include "deleted" entries)
> **AND:** there are account_types associated with entries  
> **AND:** there are tags associated with some entries  
> **AND:** there are attachments associated with some entries  
> **WHEN:** visiting GET /api/entries/
> **AND:** visiting GET /api/entries/1
> **AND:** visiting GET /api/entries/2
> **THEN:** receive a 200 status  
> **AND:** get a json response containing a list of entries  
> **AND:** in the json is a count of the total non-deleted entries in the database  
> **AND:** in the entry nodes there are entry details, including "has_attachments", "tags" and "account_type" nodes  
> **AND:** entry tags node is an array  

_GET /api/entries/ example output:_
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

_GET /api/entries/1 example output:_
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

_GET /api/entries/2 example output:_
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