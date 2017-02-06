# REST API

### Feature: GET /api/tags

**Scenario:** obtain a list of available entry tags
> **GIVEN:** there are tag values present in the database  
> **WHEN:** visiting GET /api/tags  
> **THEN:** we will receive a 200 status  
> **AND:** display a json output of tags  

_example output:_
```json
{
	"0": {"id": 1, "tag": "tag1"},
	"1": {"id": 2, "tag": "tag2"},
	"count": 2
}
```

- - -

**Scenario:** obtain a list of available entry tags when no data is present
> **GIVEN:** there are NO tag values present in the database  
> **WHEN:** visiting GET /api/tags  
> **THEN:** we will receive a 404 status  
> **AND:** display empty json output  

_example output:_
```json
[]
```
- - -

### Feature: GET /api/accounts

**Scenario:** obtain a list of available accounts
> **GIVEN:** there are accounts in the database  
> **WHEN:** visiting GET /api/accounts  
> **THEN:** receive a 200 status  
> **AND:** get a list of accounts in json format  

_example output:_
```json
{
  "0": {"id": 1, "account": "test account 1", "total": "10.00"},
  "1": {"id": 2, "account": "test account 2", "total": "0.01"},
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