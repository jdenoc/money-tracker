# Money Tracker
## Monitoring

### Run health-check process
```bash
php artisan health:check
```
This process is run as part of one of our [scheduled tasks](SETUP-TASKS.md).

---

### Review health

**CLI**
```bash
php artisan health:list
php artisan schedule-monitor:list
```

**Browser endpoints**
- /health
  - User-friendly interface for viewing health information
- /health.json
  - Health information in a JSON format 