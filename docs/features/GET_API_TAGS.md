### Feature: GET _/api/tags_

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