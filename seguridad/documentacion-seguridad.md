
# Seguridad del Sistema de Inventario

## 1. Control de acceso IAM
Se creó el rol IAM `Rol-EC2-CloudWatch-Inventario`, asociado a la instancia EC2. Este rol utiliza la política `CloudWatchAgentServerPolicy` para permitir el envío de registros y métricas a CloudWatch sin almacenar credenciales directamente en el código.

## 2. Seguridad de red
El grupo de seguridad de EC2 tiene las siguientes reglas:
- SSH (puerto 22): acceso restringido a una dirección IP específica.
- HTTP (puerto 80): permite el acceso público a la aplicación web.

## 3. Protección de almacenamiento
El bucket S3 utiliza cifrado del lado del servidor AES-256 y tiene habilitado el bloqueo de acceso público.

## 4. Protección de los datos
Los productos se almacenan en un archivo JSON ubicado fuera del directorio público de Apache. Se configuraron permisos de acceso para limitar su lectura y modificación.

## 5. Registro y auditoría
Se configuró AWS CloudTrail para registrar eventos de administración de lectura y escritura. Los registros se almacenan en un bucket S3 y permiten revisar las actividades realizadas en AWS.

## 6. Monitoreo
CloudWatch recibe registros de Apache y cuenta con una alarma que supervisa la utilización de CPU de la instancia EC2.

## 7. Protección de credenciales
No se deben almacenar claves privadas, contraseñas ni credenciales de AWS dentro del repositorio GitHub.
