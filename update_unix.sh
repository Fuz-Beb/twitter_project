#!/bin/bash -e
files=(autoload.php composer.json composer-setup.php controller instructions lib README.md tests update_unix.sh update_win.bat scripts view www)

if [ $# -eq 0 ]; then
    echo "Downloading master.zip"
    echo "----------------------"
    echo ""
    wget -O master.tar.gz http://github.com/prafiny/db-project/archive/master.tar.gz
    tar xvf master.tar.gz
    rm master.tar.gz
    for i in "${files[@]}"
    do
            rm -r -f $i
    done
    echo "Updating"
    echo "--------"
    echo ""
    rm -r -f bin
    bash db-project-master/update_unix.sh --new-version
else
    for i in "${files[@]}"
    do
            cp -r db-project-master/$i .
    done
    rm -r db-project-master
    echo "Updating composer"
    echo "-----------------"
    echo ""
    php composer-setup.php --install-dir=. --filename=composer
    echo "Updating composer packages"
    echo "-----------------"
    echo ""
    php composer update
fi

