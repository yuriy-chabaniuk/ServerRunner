# ServerRunner
Extension for Codeception to run server based on browser.

> documentation in progress

### Enabling ServerRunner

```yaml
extensions:
    enabled:
        - Ychabaniuk\ServerRunner\ServerRunner
    config:
        Ychabaniuk\ServerRunner\ServerRunner:
            serverFolder: 'libs/server/'
            driverFolder: 'libs/driver/'
            debug: false
```
