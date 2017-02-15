### Feature: GET _/api/entries_

**Scenario:** get a list of all entries when no entries exist
> **GIVEN:** there are no entry records  
> **WHEN:** visiting GET /api/entries  
> **THEN:** receive a 404 status  
> **AND:** get an empty array in json format

_example output:_
```json
[]
```

- - -

**Scenario:** get a list of all entries when entries are available
> **GIVEN:** there are 50 or less entries  
> **AND:** there are account_types associated with entries  
> **AND:** there are tags associated with some entries  
> **AND:** there are attachments associated with some entries  
> **AND:** some entries are marked "deleted"  
> **WHEN:** visiting GET /api/entries  
> **THEN:** receive a 200 status  
> **AND:** get a json response containing a list of entries  
> **AND:** in the json is a count of the entries  
> **AND:** in the entry nodes there are entry details, including "has_attachments", "tags" and "account_type" nodes  
> **AND:** entry tags node is an array  
> **AND:** entries marked as "deleted" do not appear

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
  "2": {
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
  "3": {
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