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

## Por favor no se olviden de utilizar el trello, muchas gracias 
1. Esta en el grupo chicuelos
