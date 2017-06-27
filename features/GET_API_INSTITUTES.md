### Feature GET _/api/institutions_

**Scenario**: obtain a list of institutions when there are no institutions available 
> **GIVEN:** there are no institutes in the database  
> **WHEN:** making a GET request to /api/institutes  
> **THEN:** receive a 404 response  
> **AND:** receive an empty json array   

_example output:_
```json
[]
```

- - -

**Scenario**: obtain a list of institutions
> **GIVEN:** there are initiations available in the database  
> **WHEN:** making a GET request to /api/institutes  
> **THEN:** receive a 200 response  
> **AND:** receive an a list of institutions  

_example output:_
```json
{
  "0": {"id": 1, "name": "test institute 1", "active": 1},
  "1": {"id": 2, "name": "test institute 2", "active": 1},
  "count": 2
}
```

- - -

**Scenario**: obtain a list of initiations when some are _NOT_ active
> **GIVEN:** there are initiations available in the database  
> **AND:** some of those institutions are _NOT_ active  
> **WHEN:** making a GET request to /api/institutes    
> **THEN:**  
> **AND:**  

_example output:_
```json
{
  "0": {"id": 1, "name": "test institute 1", "active": 1},
  "1": {"id": 2, "name": "test institute 2", "active": 0},
  "2": {"id": 2, "name": "test institute 2", "active": 1},
  "count": 3
}
```