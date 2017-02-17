### Feature: DELETE /api/entry/{entry_id}

**Scenario:** attempt to delete an entry when entry does not exist
> **GIVEN:** an entry_id  
> **AND:** there are _NO_ entries in the database  
> **WHEN:** visiting GET /api/entry/{entry_id} to confirm entry does _NOT_ exist  
> **AND:** visiting DELETE /api/entry/{entry_id}  
> **THEN:** receive a 404 status from GET request  
> **AND:** receive a 404 status from DELETE request  

**Scenario:** attempt to delete an entry when entry exists
> **GIVEN:** an entry_id  
> **AND:** there are entries in the database  
> **WHEN:** visiting GET /api/entry/{entry_id} to confirm entry does exist  
> **AND:** visiting DELETE /api/entry/{entry_id}  
> **AND:** visiting GET /api/entry/{entry_id} to confirm entry has been marked "deleted"  
> **THEN:** receive a 200 status from first GET request  
> **AND:** receive a 204 status from DELETE request  
> **AND:** receive a 404 status from second GET request  