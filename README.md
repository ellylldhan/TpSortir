## Sortir.com
Projet PHP Symfony "Sortir.com" @ ENI

### Installation 

```sh
cd /wamp64/www
git clone https://github.com/eperia35/TpSortir.git 
cd TpSortir
composer update
```

Pour créer la base de donnée, vous devez d'abord configurer la chaîne de connexion `DATABASE_URL` dans le fichier `.env` ou `.env.local`, puis exécuter :

```sh
php bin/console doctrine:database:create 
php bin/console doctrine:schema:update --force
```

[http://localhost/TpSortir/public/](http://localhost/TpSortir/public/)
