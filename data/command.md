php ./vendor/doctrine/doctrine-module/bin/doctrine-module orm:convert-mapping --force --from-database annotation ./data/generated
php ./vendor/doctrine/doctrine-module/bin/doctrine-module orm:schema-tool:update --dump-sql > data/data.sql
php ./vendor/doctrine/doctrine-module/bin/doctrine-module orm:validate
sudo docker exec -it roi-push_php_1 bash

msgfmt messages.en.po -o messages.en.mo

token
36ac75e73305b2301081d06ff269619c9be8e08b

import api:
php -f public/index.php apiImport 66
php -f public/index.php apiImport all

ssh -i ~/.ssh/id_rsa -p 6633 root@135.181.202.233
ssh -i ~/.ssh/id_rsa -p 6633 root@5.189.183.135
ssh -i ~/.ssh/id_rsa -p 6633 root@161.97.140.148


iptables -I INPUT -i eth0 -p tcp --dport 9200 -s 84.157.229.56 -j ACCEPT
redis
iptables -I INPUT -i eth0 -p tcp --dport 6379 -s 84.157.236.113 -j ACCEPT


=============================== DOCKER =========================================
docker exec -it wsphp_ssp_mng bash



=============================== NEW PROJECT =========================================
mySQL:
mysql -u root -p test123Pa
CREATE USER 'root'@'%' IDENTIFIED BY 'test123Pa';
ALTER USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY 'test123Pa';

GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;




ALTER TABLE `user` DROP FOREIGN KEY `FK_user_category`;
ALTER TABLE `user` DROP COLUMN `category_id`;
DROP TABLES attribute, attribute_category_xref, attribute_option, attribute_tab, attribute_type, category, product;



