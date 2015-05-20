Configuration Reference
=======================

In the configuration (```app/config/config.yml``` for example) you need to configure the ```chatea_secure``` section like this:

All configuration options are listed below::

```yaml
# app/config/config.yml

chatea_secure:
    app_auth:
        client_id:                  %chatea_client_id%			#the client id of chatsfree to call the api
        secret:                     %chatea_secret_id%			#secrete for the client
        enviroment:                 %chatea_enviroment%			#the environment you want to execute
    api_endpoint:                   %api_endpoint%				#the endpoint url, generally https://api.chatsfree.net/
    homepage_path:                  homepage					# param to redirect in loginAction when user is logged, default value = "/"
   
```
