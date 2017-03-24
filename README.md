RPMON
=====

Monitor de estado para sistemas basados en Debian (Ubuntu, Raspbian, etc). Guarda el uso de RAM, de CPU y la temperatura de esta. Lo muestra en gráficos haciendo uso de FusionCharts.



Instalación
-----------

* Copiar la carpeta con todos los archivos en el directorio del apache.

* Ejecutar el script sql (*rpmon.sql*) contra una base de datos y configurar el archivo *bbdd.php* con los datos de acceso.

* Habrá que crear una nueva entrada en el cron para que se vayan guardando los datos.

* Para controlar la temperatura en Ubuntu hay que instalar los sensores:
```
		sudo apt-get install lm-sensors
		sudo sensors-detect
```

* Probado en Raspbian, Ubuntu y Trisquel.



Screenshot
----------

![Screenshot](screenshot.png)







© [MdeMoUcH](http://www.twitter.com/mdemouch) | [La Gran M](http://www.lagranm.com) | [Ubuntu Fácil](http://www.ubuntufacil.com)