# Money Tracker
## F.A.Q.
### Production Updates

**Step 2**  
You may not have your ssh key setup.
```bash
# Confirm ssh key is active in your ssh-agent
ssh-add -l
# It's not? Confirm you have an ssh key
ll ~/.ssh/
# No? Confirm you have one, add it to your ssh-agent
ssh-add ~/.ssh/{private-ssh-key-file-name}
```

If it turns out you don't actually have an ssh key, consider using [these instructions](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent) to create one.

---

**Step 3.c** & **Step 4**  
If you are having issues installing using npm or using the build script, you likely have memory issues.  
To resolve this prefix your npm command with:
```bash
NODE_OPTIONS="--max_old_space_size=2048"
```

For example:
```bash
NODE_OPTIONS="--max_old_space_size=2048" npm run build-dev
```

If you continue to have issues, (and if possible,) increase the value.  
You may not be able to. Try a different system and perform tasks there, transferring files from there.

---

**Step 5**  
Sometime you will be alerted that you can't clear the cache. This is usually a permission issue.
1. Identify what groups your user belongs to
    ```bash
    groups
    ````
2. Find out the existing permissions
    ```bash
    ls -lah storage/framework/cache/data
    ```
3. Confirm that the group associated with the storage/framework/cache/data directory matches one of the groups your user is associated with.
4. You will likely need to add group write access
    ```bash
    chmod g+w -R storage/framework/cache/data
    ```
    1. Alternatively you could delete the cache data directory
        ```bash
        rm -rf storage/framework/cache/data
        ```
