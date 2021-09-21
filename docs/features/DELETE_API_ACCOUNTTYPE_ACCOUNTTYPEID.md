### Feature : DELETE /api/account-type/{account_type_id}

**Scenario:** attempt to disable account-type if it doesn't exist
> **GIVEN:** account-type doesn't exist  
> **WHEN:** send DELETE request /api/account-type/{account_type_id}  
> **THEN:** receive a 404 status  

- - -

**Scenario:** attempt to disable account-type
> **GIVEN:** an account-type exists  
> **AND:** an account associated with the account-type exists  
> **WHEN:** sending GET request to /api/account/{account_id}  
> **AND:** send DELETE request /api/account-type/{account_type_id}  
> **AND:** send GET request to /api/account/{account_id}  
> **THEN:** receive a 200 status  
> **AND:** response from first GET request contains json containing account details  
> **AND:** account_type has a disabled node that contains the value false  
> **AND:** receive a 204 status  
> **AND:** receive a 200 status  
> **AND:** response from second GET request contains json containing account details
> **AND:** account_type has a disabled node that contains the value true  