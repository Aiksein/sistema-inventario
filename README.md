
# Sistema de Inventario en AWS

## 1. Descripción del proyecto

Este proyecto consiste en el desarrollo e implementación de un sistema web de inventario para administrar productos de una organización.

La aplicación permite registrar, consultar, modificar y eliminar productos, además de identificar aquellos que tienen bajo stock.

El sistema fue desplegado en Amazon Web Services (AWS), utilizando servicios de infraestructura, monitoreo y seguridad.

## 2. Objetivo

Implementar un sistema de inventario web utilizando servicios AWS y prácticas DevOps, incorporando infraestructura como código, monitoreo, pruebas automatizadas y medidas de seguridad.

## 3. Funcionalidades

- Registrar productos.
- Consultar el inventario.
- Modificar productos y cantidades.
- Eliminar productos.
- Identificar productos con bajo stock.
- Mostrar el resumen del inventario.

## 4. Tecnologías utilizadas

- AWS EC2: alojamiento del servidor web.
- Amazon S3: almacenamiento y protección del bucket.
- AWS CloudFormation: creación de infraestructura mediante código.
- Amazon CloudWatch: monitoreo y registros.
- AWS CloudTrail: registro de actividades.
- IAM: administración de permisos.
- Apache: servidor web.
- PHP: desarrollo de la aplicación.
- JSON: almacenamiento de productos.
- GitHub: control de versiones y documentación.

## 5. Arquitectura general

El sistema funciona mediante una instancia EC2 que ejecuta Apache y PHP. Los productos se almacenan en un archivo JSON ubicado fuera del directorio público del servidor.

CloudWatch recibe los registros de Apache y supervisa el uso de CPU. CloudTrail registra las actividades de administración realizadas en AWS.

## 6. Pruebas automatizadas

Se desarrolló un archivo de pruebas PHP para comprobar las operaciones principales del inventario.

Resultados obtenidos:

- Inventario inicial vacío: aprobado.
- Guardar producto: aprobado.
- Cargar producto: aprobado.
- Detectar bajo stock: aprobado.
- Actualizar stock: aprobado.
- Eliminar producto: aprobado.

**Resultado final: 6 de 6 pruebas aprobadas.**

## 7. Seguridad

El proyecto incorpora:
- Rol IAM asociado a EC2.
- Acceso SSH restringido a una IP específica.
- Cifrado AES-256 en S3.
- Bloqueo de acceso público al bucket.
- Protección del archivo JSON mediante permisos del sistema.
- Registro de actividades mediante CloudTrail.
- Monitoreo mediante CloudWatch.

Más información en `seguridad/documentacion-seguridad.md`.

## 8. Estructura del repositorio

- `infraestructura/`: plantilla CloudFormation.
- `aplicacion/`: código PHP del sistema.
- `monitoreo/`: configuración de CloudWatch Agent.
- `tests/`: pruebas automatizadas.
- `seguridad/`: documentación de seguridad.

## 9. Conclusión

El proyecto permitió implementar un sistema de inventario web en AWS, incorporando herramientas de infraestructura como código, monitoreo, pruebas automatizadas y seguridad.

También permitió aplicar conocimientos relacionados con servicios en la nube y prácticas DevOps en un caso práctico.
