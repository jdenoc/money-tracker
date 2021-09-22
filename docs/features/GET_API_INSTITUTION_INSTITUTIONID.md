### Feature GET _/api/institution/{institution_id}_

**Scenario**: obtain information on a specific institution based on a provided ID, when there are said institution does not exist 
> **GIVEN:** we have an ID  
> **AND:** there is no institution associated with said ID  
> **WHEN:** making a GET request to /api/institution/{institution_id}  
> **THEN:** receive a 404 response  
> **AND:** receive an empty json array

_example output:_
```json
[]
```

- - -

**Scenario**: obtain information on a specific institution based on a provided ID and there are no associated accounts 
> **GIVEN:** we have an ID  
> **AND:** there is an institution associated with said ID  
> **AND:** there are no accounts associated with said institution  
> **WHEN:** making a GET request to /api/institution/{institution_id}  
> **THEN:** receive a 200 response  
> **AND:** get a json response that contains institution data

_example output:_
```json
{
    "id":1,
    "name":"test institution 1",
    "active":true,
    "create_stamp":"2017-01-01T00:00:00+00:00",
    "modified_stamp":"2017-12-24T23:59:59+00:00",
    "accounts":[]
}
```

- - -

**Scenario**: obtain information on a specific institution based on a provided ID and there are associated accounts 
> **GIVEN:** we have an ID  
> **AND:** there is an institution associated with said ID  
> **AND:** there are accounts associated with said institution  
> **WHEN:** making a GET request to /api/institution/{institution_id}  
> **THEN:** receive a 200 response  
> **AND:** get a json response that contains institution data

_example output:_
```json
{
    "id":1,
    "name":"test institution 1",
    "active":true,
    "create_stamp":"2017-01-01T00:00:00+00:00",
    "modified_stamp":"2017-12-24T23:59:59+00:00",
    "accounts":[
        {
            "id":1,
            "name":"test account 1",
            "disabled": false,
            "total":"10.00"
        },{
            "id":2,
            "name":"test account 2",
            "disabled": false,
            "total":"-10.00"
        }
    ]
}
```