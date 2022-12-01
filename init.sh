if [ -f ".env" ]; then
    echo ".env exist"
else
    cp .env.dev .env
fi

#安裝套件
composer install
#修改cas套件
sed -i "s/_server\['base_url'\]\ \=\ 'https/_server\['base_url'\]\ \='http/g" vendor/apereo/phpcas/source/CAS/Client.php 

#supervisor
sh -c 'echo "files = $(pwd)/supervisor/*.conf" >> /etc/supervisor/supervisord.conf'