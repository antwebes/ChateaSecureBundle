Configuration Reference
=======================

All configuration options are listed below::

```yaml
# app/config/config.yml

chatea_secure:
    app_auth:
        client_id:                      %chatea_client_id%
        secret:                         %chatea_secret_id%
        enviroment:                     %chatea_enviroment%
    api_endpoint:                       %api_endpoint%
    homepage_path:                      homepage						# param to redirect in loginAction when user is logged, default value = "/"
   
```
