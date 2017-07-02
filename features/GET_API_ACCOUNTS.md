### Feature: GET _/api/accounts_

**Scenario:** obtain a list of available accounts
> **GIVEN:** there are accounts in the database  
> **WHEN:** visiting GET /api/accounts  
> **THEN:** receive a 200 status  
> **AND:** get a list of accounts in json format  

_example output:_
```json
{
  "0": {"id": 1, "name": "test account 1", "total": "10.00"},
  "1": {"id": 2, "name": "test account 2", "total": "0.01"},
  "count": 2
}
```

- - -
     
**Scenario:** obtain a list of available accounts when no data is present
> **GIVEN:** there are NO accounts in the database  
> **WHEN:** visiting GET /api/accounts  
> **THEN:** receive a 404 status  
> **AND:** get an empty array in json format

_example output:_
```json
[]
```