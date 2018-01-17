# Readme

This module is mainly developed for the use in the [Mapbender Project](https://github.com/mapbender/mapbender-starter) but should also work fine in other projects.
There is an example security.yml and parameter.yml in the example directory.





# Konfiguration LDAP im Mapbender

Um einen Verzeichnisdienst über LDAP im Mapbender einzubinden, müssen einige Änderungen an der Konfiguration vorgenommen werden:


Zuerst  müssen in der paramter.yml folgende Parameter eingetragen werden
Alle Parameter befinden sich im ldap Namespace :
| Parameter        | Beschreibung           | Default  |
| ------------- |:-------------:| -----:|
| host      | Hostname des Verzeichnisdienstes | - |
| port    | Port des Verzeichnisdienstes     |   - |
| useSSL    | Soll SSL Verschlüsselung bei derVerbindung genutzt werden      |   true |
| useTLS    | Soll TLS Verschlüsselung bei derVerbindung genutzt werden      |   true |
| bind.dn | Distinguished Name des Lesendennutzers      |    - |
| bind.password| Passwort des Lesendennutzers     |    - |
| user.query |  Query, um nach Nutzern zu suchen |         - |
| user.baseDn | Distinguished Name eines Nutzers    |    - |
| user.nameAttribut| Attribut das den Gruppennamen enthält     |    - |
| group.query |   Query, um nach Gruppen zu suchen      |  -  |
| group.nameAttribute | Attribut das den Gruppennamen enthält     |    - |
| group.filter | Filter, um Suche nach Gruppen einzuschränken.   |    - |
| `Tabelle 1. - Auflistung aller benötigten Parameter` |



Zusätzlich muss die security.yml der Mapbender-Installation angepasst werden. Hier muss ein LDAP-Client als [Symfonyservice](https://symfony.com/doc/2.8/service_container.html) eingerichtet werden. Folgend ist beispielhaft die Definition für den symfony-eigenen LDAP-Client mit den in Schritt 1 genutzten Parametern. 


``` 

services:
    ldapClient:
        class: Symfony\Component\Ldap\LdapClient
        arguments:
            - %ldap.host% #Address to LDAPServer
            - %ldap.port% #Port where LDAPServer is listening
            - %ldap.version% #LDAP Protocol version
            - %ldap.useSSL%          # SSL #Use SSL
            - %ldap.useTLS%          # SSL #Use SSL

``` 

Weiter muss ein LDAPUserProvider installiert und konfiguriert werden. Hier empfiehlt es sich den [WhereGroup/SymfonyMultiEncoderLDAPProvider](https://github.com/WhereGroup/SymfonyMultiEncoderLDAPProvider) zu nutzen. Dieser ist direkt für den Mapbender entwickelt und steht als composer-Paket zur Verfügung. Er kann mit

 `composer require WhereGroup/SymfonyMultiEncoderLDAPProvider` 

zum Projekt hinzugefügt werden.
Die Konfiguration erfolgt wie im folgendem Beispiel.  Hierbei ist zu beachten, dass der Wert `@ldapClient` mit dem Namen des zuvor definierten LDAPClient-Service gefüllt werden muss. Alle anderen Werte sind in der Tabelle 1. beschrieben.




``` 
    mb.ldap.userProvider:
            class: Wheregroup\Component\LdapMultiEncoderUserProvider
            arguments:
                - @ldapClient
                - %ldap.user.base_dn%
                - %ldap.bind.dn%
                - %ldap.bind.pwd%
                - %ldap.group.defaultGroups%
                - %ldap.user.nameAttribute%
                - %ldap.user.query% #dn
                - %ldap.group.dn%
                - %ldap.group.query%
                - %ldap.group.nameAttribute%
```

Danach  muss noch der neu eingerichtete UserProvider registriert werden. Dafür muss in der security.yml der Key providers , um folgende Wert erweitert werden.
```

    ldapProvider:
        id: mb.ldap.userProvider
```
Zu letzt muss noch der ldap-login in den FireWall-Definitionen aktiviert werden. Hierzu muss in der Firewall-konfiguration der Block fom_login mit folgender Code ausgetauscht werden.

```

form_login_ldap:

                check_path: /user/login/check
                login_path: /user/login
                service:  ldapClient
                dn_string: %dn_search%

```
Wenn neben des LDAP-Logins noch zusätzlich, der standard Mapbender-Login basierend auf der fom_user Tabelle genutzt werden soll. Darf der Codeblock fom_login nicht ausgetuascht werden. Dann wird der Block  form_login_ldap zusätzlich hinzugefügt.


## Nutzung von mehreren Password Encodern innerhalb eines Verzeichnisses 

Falls innerhalb des Verzeichnisdienstes die Möglichkeit genutzt wird für unterschiedliche  Passwörter verschiednene Encoder zu nutzen, müssen diese in der `security.yml` konfiguriert werden. Jeder Encoder der genutzt werden soll, wird als benannter Encoder definiert.





Hierbei muss der Name des Encoders exakt so lauten, wie dieser auch im Verzeichnisdienst lautet z.B. wenn der Wert für das  Passwort so aussieht.

``` 
{SHA512: 9a8cda40689d57f401ac182f034573d20360db3c62bb3338f83e8c11bd83bdd1
8440d1b066a0387d0a466bb945c8b6741d6d11dffa154a48b5c0fae755477b86 }
```

muss der Encoder in der security.yml auch SHA512 heißen.
Folgend eine Konfiguration für plaintext und SHA512 Encoder.

```

    encoders:
      FOM\UserBundle\Entity\User: plaintext
      plaintext:
           algorithm: plaintext
      SHA512:
          algorithm: sha512
          iterations: 1
```

Falls  nur ein Encoder genutzt werden soll, kann dieser direkt an die Entitiy gebunden werden.

```
encoders:
      FOM\UserBundle\Entity\User: SHA512
```


## Beispiel Konfiguration der Parameter 
```

    ldap.host: localhost # Host of the LDAP Directory.
    ldap.port: 389 # Port number (default: 389).
    ldap.version: 3 # LDAP Version (default: 3)
    ldap.bind.dn: cn=admin,dc=wheregroup,dc=com # User who connects to the LDAP (DN).
    ldap.bind.password: root # His/Her password.
    ldap.user.query: (&(cn={username})(objectclass=top))
    ldap.user.baseDn: ou=user,dc=wheregroup,dc=com # Where to find users to authenticate with?
    ldap.user.nameAttribute: cn # Attribute that represents the typed in username in login-form
    ldap.group.baseDn: ou=groups,dc=wheregroup,dc=com
    ldap.group.query: member={uid_key_group}={username},ou=user,dc=wheregroup,dc=com
    ldap.group.nameAttribute: cn
    ldap.group.filter: (objectclass=top)

```








