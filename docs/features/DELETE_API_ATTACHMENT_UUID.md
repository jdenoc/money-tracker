### Feature: DELETE /api/attachment/{uuid}

**Scenario:** attempt to delete an attachment record when no record exists
> **GIVEN:** an attachment uuid  
> **AND:** no attachment records exist  
> **WHEN:** visiting DELETE /api/attachment{uuid}    
> **THEN:** we will receive a 404 status  

- - -

**Scenario:** attempt to delete an attachment record when a matching record exists
> **GIVEN:** an attachment uuid  
> **AND:** an attachment record exists with an entry_id value  
> **AND:** there is an entry that corresponds to that entry_id value  
> **WHEN:** visiting GET /api/entry/{entry_id} to confirm attachment exists and is associated with the entry  
> **AND:** visiting DELETE /api/attachment{uuid} to delete the attachment record  
> **AND:** visiting GET /api/entry/{entry_id} to confirm the attachment record has been deleted  
> **THEN:** we will receive a 200 status from the first GET request  
> **AND:** there is an attachment record with the UUID matching that which we plan to delete  
> **AND:** we will receive a 204 status from the DELETE request  
> **AND:** we will receive a 200 status from the second GET request  
> **AND:** the attachment we requested be deleted does not appear in the entry attachments node