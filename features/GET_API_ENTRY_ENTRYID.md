### Feature: GET _/api/entry/{entry_id}_

**Scenario:** attempt to retrieve an entry when no data is available
> **GIVEN:** an entry_id  
> **AND:** there are no entries in the database  
> **WHEN:** visiting GET /api/entry/{entry_id}  
> **THEN:** receive a 404 status  
> **AND:** get an empty array in json format

_example output:_
```json
[]
```

- - -

**Scenario:** retrieve an entry record
> **GIVEN:** an entry_id
> **AND:** there is an entry in the database
> **AND:** there is an account_type record in the database
> **AND:** there are tags in the database that are associated with the entry
> **AND:** there are attachments in the database that are associated with the entry
> **WHEN:** visiting GET /api/entry/{entry_id}
> **THEN:** receive a 200 status
> **AND:** get a json response with entry data
> **AND:** entry data will contain "attachments" and "tags" array nodes

_example output:_
```json
{
  "id": 1,
  "entry_date": "1970-01-01",
  "entry_value": "0.01",
  "memo": "entry test",
  "expense": "1",
  "confirm": 0,
  "account_type": 1,
  "tags": [
    {"id": 1, "tag": "xxx"},
    {"id": 2, "tag": "yyy"},
    {"id": 3, "tag": "zzz"}
  ],
  "attachments": [
    {"attachment": "test1.txt", "uuid": "baa9302e-6ebc-437b-b5c7-32075e7a3ddd"},
    {"attachment": "test1.txt", "uuid": "a4c5286d-6b31-4ed3-8f3f-9a8e17e753f3"}
  ],
  "create_stamp": "1970-01-01T00:00:00+00:00",
  "modified_stamp": "1970-01-01T00:00:01+00:00"
}
```

- - -

**Scenario:** retrieve an entry record but with no tags
> **GIVEN:** an entry_id
> **AND:** there is an entry in the database
> **AND:** there is an account_type record in the database
> **AND:** there are no tags in the database that are associated with the entry
> **AND:** there are attachments in the database that are associated with the entry
> **WHEN:** visiting GET /api/entry/{entry_id}
> **THEN:** receive a 200 status
> **AND:** get a json response with entry data
> **AND:** entry data will contain "attachments" and "tags" array nodes
> **AND:** "tags" array node is empty

_example output:_
```json
{
  "id": 1,
  "entry_date": "1970-01-01",
  "entry_value": "0.01",
  "memo": "entry test",
  "expense": "1",
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

- - -

**Scenario:** retrieve an entry record but with no attachments
> **GIVEN:** an entry_id
> **AND:** there is an entry in the database
> **AND:** there is an account_type record in the database
> **AND:** there are tags in the database that are associated with the entry
> **AND:** there are no attachments in the database that are associated with the entry
> **WHEN:** visiting GET /api/entry/{entry_id}
> **THEN:** receive a 200 status
> **AND:** get a json response with entry data
> **AND:** entry data will contain "attachments" and "tags" array nodes
> **AND:** "attachments" array node is empty

_example output:_
```json
{
  "id": 1,
  "entry_date": "1970-01-01",
  "entry_value": "0.01",
  "memo": "entry test",
  "expense": "1",
  "confirm": 0,
  "account_type": 1,
  "tags": [
    {"id": 1, "tag": "xxx"},
    {"id": 2, "tag": "yyy"},
    {"id": 3, "tag": "zzz"}
  ],
  "attachments": [],
  "create_stamp": "1970-01-01T00:00:00+00:00",
  "modified_stamp": "1970-01-01T00:00:01+00:00"
}
```

- - -

**Scenario:** retrieve an entry record but with no tags and no attachments
> **GIVEN:** an entry_id
> **AND:** there is an entry in the database
> **AND:** there is an account_type record in the database
> **AND:** there are no tags in the database that are associated with the entry
> **AND:** there are no attachments in the database that are associated with the entry
> **WHEN:** visiting GET /api/entry/{entry_id}
> **THEN:** receive a 200 status
> **AND:** get a json response with entry data
> **AND:** entry data will contain "attachments" and "tags" array nodes
> **AND:** "tags" array node is empty
> **AND:** "attachments" array node is empty

_example output:_
```json
{
  "id": 1,
  "entry_date": "1970-01-01",
  "entry_value": "0.01",
  "memo": "entry test",
  "expense": "1",
  "confirm": 0,
  "account_type": 1,
  "tags": [],
  "attachments": [],
  "create_stamp": "1970-01-01T00:00:00+00:00",
  "modified_stamp": "1970-01-01T00:00:01+00:00"
}
```

**Scenario:** attempt to retrieve an entry when entry is marked as "deleted"
> **GIVEN:** an entry_id  
> **AND:** there is an entry in the database
> **AND:** then entry is marked "deleted"
> **WHEN:** visiting GET /api/entry/{entry_id}  
> **THEN:** receive a 404 status  
> **AND:** get an empty array in json format

_example output:_
```json
[]
```