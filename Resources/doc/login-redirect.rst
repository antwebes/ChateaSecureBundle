# Login Redirect
para ir a una pagina concreta después de login debemos configurar en
```
security
	antwebs_chateasecure_login
		default_target_path /ruta-redirect
```
para ir a la pagina anterior al formulario de login (donde estábamos antes de que apareciera el formulario)
```
security
	antwebs_chateasecure_login
		use_referer true
```
si queremos personalizar más por ejemplo que valla a la pagina de editar perfil si este no se completo debemos sobrescribir la clase de symfony DefaultAuthenticationSuccessHandler y concretamente el método onAuthenticationSuccess , también debemos sobrescribir el método createAuthenticationSuccessHandler  en la factory personalizada  (Ant\Bundle\ChateaSecureBundle\DependencyInjection\Factory\SecurityFactory
) en el securityBandle

por ejemplo :
```
    protected function createAuthenticationSuccessHandler($container, $id, $config)
    {

        if (isset($config['success_handler'])) {
            return $config['success_handler'];
        }

        $successHandlerId = 'security.authentication.success_handler.'.$id.'.'.str_replace('-', '_', $this->getKey());

        $successHandler = $container->setDefinition($successHandlerId, new DefinitionDecorator('chata_auth_success_handler'));
        $successHandler->replaceArgument(1, array_intersect_key($config, $this->defaultSuccessHandlerOptions));
        $successHandler->addMethodCall('setProviderKey', array($id));
        return $successHandlerId;
    }
```
también lo como servicio en services.xml (en el security bundle):

```
        <service id="chata_auth_success_handler" class="Ant\Bundle\ChateaSecureBundle\Security\Http\Authentication\AuthenticationSuccessHandler">
            <argument type="service" id="security.http_utils" />
            <argument type="collection" />
        </service>
```

//actualizar Header  referer  desde un controlador añadimos el referer a la session
```
if(!$request->getSession()->get('referer')){
            $request->getSession()->set('referer', $this->getRequest()->headers->get('referer'));
}
```

# Autologin

Si se tiene el access token de un usuario se puede loguear al usuario a traves de cualquier URL añadiendo el parametro querystring ```autologin``` con el access token por ejemplo:

```
http://misuperchatsocial.com/usuarios?autologin?ACCESS_TOKEN
```