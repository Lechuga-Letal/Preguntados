# Preguntados PW2.

## Instalación
1. Crear BD e importar `database.sql`.
2. Copiar el contenido del zip en el servidor.
3. Esta vez no hay ningun usuario base.

## Cambios al apache:
1. Buscar el archivo httpd.CONF dentro de D:\xampp\apache\conf\
2. Abrir el archivo con bloc de notas
3. Encontrar las siguientes lineas:
   DocumentRoot "D:/xampp/htdocs"
  <Directory "D:/xampp/htdocs">
4. Modificar las direcciones agregando "/Preguntados" al final. Asi:
   DocumentRoot "D:/xampp/htdocs/Preguntados"
  <Directory "D:/xampp/htdocs/Preguntados">
5. Reiniciar el Xampp

## JpGraph implementacion
En caso de ser la primera vez en entrar a la vista de admin post la implementacion del JpGraph es muy probable que tengan que realizar un par de cambios para su correcta visualizacion:

1. En caso de encontrarse con el error "Fatal error: Uncaught Error: Undefined constant "IMG_PNG" in...", tienen que: 
  A. Ir a D:\xampp\php\php.ini y abrir dicho archivo
  B. Buscar "gd" y encontrar la linea: ;extension=gd
  C. Descomentar la linea eliminando el ; al comienzo
  D. Reiniciar Apache

2. Al encontrarse con el error 25107, para solucionarlo solo tienen que permitir que todos los usuarios puedan modificar la carpeta graficos dentro de public. 
Eso se hace desde explorador de archivos o con el cmd (Vease ultima clase a).

## Por favor no se olviden de utilizar el trello, muchas gracias 
1. Esta en el grupo chicuelos
