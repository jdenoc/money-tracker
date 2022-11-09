# Money Tracker
## Debugging

Make sure xdebug is enabled. It is enabled by default when building container images.
```bash
docker-compose exec application php -v | grep -i xdebug
```

**TODO:** write how to hook up IDE to docker xdebug.

Open browser and go to http://localhost:7900. This will allow you to view tests as they run from a browser window.

