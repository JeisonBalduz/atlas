-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: rrhh
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `asignacionbeneficios`
--

DROP TABLE IF EXISTS `asignacionbeneficios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asignacionbeneficios` (
  `id_beneficio` int(11) NOT NULL,
  `idPersonal` int(11) DEFAULT NULL,
  `idNino` int(11) DEFAULT NULL,
  `idbeneficio` int(11) DEFAULT NULL,
  `horasExtra` int(11) DEFAULT NULL,
  `DiasFeriados` int(11) DEFAULT NULL,
  `fecha` int(11) NOT NULL,
  `hora` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignacionbeneficios`
--

LOCK TABLES `asignacionbeneficios` WRITE;
/*!40000 ALTER TABLE `asignacionbeneficios` DISABLE KEYS */;
/*!40000 ALTER TABLE `asignacionbeneficios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asignarvacaciones`
--

DROP TABLE IF EXISTS `asignarvacaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asignarvacaciones` (
  `id_vacaciones` int(11) NOT NULL AUTO_INCREMENT,
  `idEmpleado` int(11) NOT NULL,
  `activo` int(2) DEFAULT NULL,
  `ano` int(4) NOT NULL,
  `dias` int(2) NOT NULL,
  `fecha` varchar(20) NOT NULL,
  `hora` varchar(20) NOT NULL,
  PRIMARY KEY (`id_vacaciones`),
  KEY `idEmpelado` (`idEmpleado`),
  CONSTRAINT `asignarvacaciones_ibfk_1` FOREIGN KEY (`idEmpleado`) REFERENCES `datosempleados` (`id_empleados`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignarvacaciones`
--

LOCK TABLES `asignarvacaciones` WRITE;
/*!40000 ALTER TABLE `asignarvacaciones` DISABLE KEYS */;
INSERT INTO `asignarvacaciones` VALUES (1,1,0,2014,3,'2025-02-26','00:58:17'),(4,73,0,2015,10,'2025-02-26','01:31:58 AM'),(5,73,0,2017,3,'2025-02-26','01:31:58 AM');
/*!40000 ALTER TABLE `asignarvacaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `atencionmedica`
--

DROP TABLE IF EXISTS `atencionmedica`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `atencionmedica` (
  `id_atencion` int(11) NOT NULL AUTO_INCREMENT,
  `idPersonal` int(11) DEFAULT NULL,
  `idNino` int(11) DEFAULT NULL,
  `idMedico` int(11) DEFAULT NULL,
  `idPsicologo` int(11) DEFAULT NULL,
  `atencion` varchar(150) NOT NULL,
  PRIMARY KEY (`id_atencion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atencionmedica`
--

LOCK TABLES `atencionmedica` WRITE;
/*!40000 ALTER TABLE `atencionmedica` DISABLE KEYS */;
/*!40000 ALTER TABLE `atencionmedica` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditoria`
--

DROP TABLE IF EXISTS `auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auditoria` (
  `id_auditoria` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora` varchar(15) NOT NULL,
  `tipo_evento` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `tabla_afectada` varchar(255) DEFAULT NULL,
  `ip` varchar(15) NOT NULL,
  `navegador` varchar(20) NOT NULL,
  `sistemaOperativo` varchar(30) NOT NULL,
  `arquitectura` varchar(6) NOT NULL,
  PRIMARY KEY (`id_auditoria`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `usuario` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=271 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria`
--

LOCK TABLES `auditoria` WRITE;
/*!40000 ALTER TABLE `auditoria` DISABLE KEYS */;
INSERT INTO `auditoria` VALUES (1,'00000298',1,'2025-02-22','01:07:44 AM','Desactivar cargo','el usuario Jeison12345 ha  desactivado el cargo Pppaff en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(2,'00000140',1,'2025-02-22','01:07:50 AM','Activar cargo','el usuario Jeison12345 ha  activado el cargo Pppaff en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(3,'00000291',1,'2025-02-23','00:49:33 AM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(4,'00000278',1,'2025-02-23','00:37:28 AM','Registrar','el usuario Jeison12345 ha registrado la dependencia JEADSAD en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(5,'00000089',1,'2025-02-23','01:10:02 AM','Editar dependencia','el usuario Jeison12345 ha editado la dependencia JEADSAD en el sistema por Emili',NULL,'::1','Chrome','Windows 10','64-bit'),(6,'00000709',1,'2025-02-23','01:19:58 AM','Desactivar dependencia','El usuario Jeison12345 ha desactivado la dependencia Emili en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(7,'00000468',1,'2025-02-23','01:20:02 AM','Activar dependencia','El usuario Jeison12345 ha activado la dependencia Emili en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(8,'00000232',1,'2025-02-23','01:20:19 AM','Editar dependencia','El usuario Jeison12345 ha editado la dependencia Emili en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(9,'00000007',1,'2025-02-23','01:21:42 AM','Registrar dependencia','El usuario Jeison12345 ha registrado la dependencia Ewqeqe en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(10,'00000841',1,'2025-02-23','02:33:23 AM','Activar estatus','El usuario Jeison12345 ha activado el estatus Estudiante en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(11,'00000619',1,'2025-02-23','02:33:26 AM','Desactivar estatus','El usuario Jeison12345 ha desactivado el estatus Estudiante en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(12,'00000411',1,'2025-02-23','02:42:13 AM','Editar estatus','El usuario Jeison12345 ha editado el estatus Contradado por Contradadoss en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(13,'00000291',1,'2025-02-23','02:42:44 AM','Editar estatus','El usuario Jeison12345 ha editado el estatus Contradadoss por Contradado en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(14,'00000054',1,'2025-02-23','02:42:48 AM','Desactivar estatus','El usuario Jeison12345 ha desactivado el estatus Contradado en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(15,'00000924',1,'2025-02-23','02:42:52 AM','Activar estatus','El usuario Jeison12345 ha activado el estatus Contradado en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(16,'00000928',1,'2025-02-23','02:44:52 AM','Editar estatus','El usuario Jeison12345 ha editado el estatus  por  en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(17,'00000385',1,'2025-02-23','02:48:20 AM','Activar estatus','El usuario Jeison12345 ha activado el estatus Estudiante en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(18,'00000949',1,'2025-02-23','02:48:22 AM','Desactivar estatus','El usuario Jeison12345 ha desactivado el estatus Estudiante en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(19,'00000548',1,'2025-02-23','02:48:29 AM','Editar estatus','El usuario Jeison12345 ha editado el estatus Contradado por Jeison en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(20,'00000347',1,'2025-02-23','02:48:32 AM','Desactivar estatus','El usuario Jeison12345 ha desactivado el estatus Jeison en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(21,'00000399',1,'2025-02-23','02:48:36 AM','Editar estatus','El usuario Jeison12345 ha editado el estatus Jeison por Jeisonwww en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(22,'00000928',1,'2025-02-23','02:48:38 AM','Activar estatus','El usuario Jeison12345 ha activado el estatus Jeisonwww en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(23,'00000601',1,'2025-02-23','02:49:47 AM','Registrar estatus','El usuario Jeison12345 ha registrado el Jeison en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(24,'00000966',1,'2025-02-23','03:12:15 AM','Activar departamento','El usuario Jeison12345 ha activado el departamento Penitenciario Luisa Cáceres de Arismendi en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(25,'00000904',1,'2025-02-23','03:12:17 AM','Desactivar departamento','El usuario Jeison12345 ha desactivado el departamento Penitenciario Luisa Cáceres de Arismendi en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(26,'00000012',1,'2025-02-23','03:14:56 AM','Editar cargo','El usuario Jeison12345 ha editado el cargo Pppaff en el sistema por Pppaff.',NULL,'::1','Chrome','Windows 10','64-bit'),(27,'00000229',1,'2025-02-23','03:16:55 AM','Editar dependencia','El usuario Jeison12345 ha editado la dependencia Ewqeqe en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(28,'00000635',1,'2025-02-23','03:24:19 AM','Editar dependencia','El usuario Jeison12345 ha editado la dependencia Ewqeqe en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(29,'00000813',1,'2025-02-23','03:24:23 AM','Editar dependencia','El usuario Jeison12345 ha editado la dependencia Ewqeqew en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(30,'00000394',1,'2025-02-23','03:24:40 AM','Activar departamento','El usuario Jeison12345 ha activado el departamento Penitenciario Luisa Cáceres de Arismendi en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(31,'00000199',1,'2025-02-23','03:24:41 AM','Desactivar departamento','El usuario Jeison12345 ha desactivado el departamento Penitenciario Luisa Cáceres de Arismendi en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(32,'00000110',1,'2025-02-23','03:24:50 AM','Editar departamento','El usuario Jeison12345 ha editado el departamento Ferroviario por Ferroviariow en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(33,'00000851',1,'2025-02-23','03:34:25 AM','Registrar departamento','El usuario Jeison12345 ha registrado el departamento Depe en el sistema.',NULL,'::1','Chrome','Windows 10','64-bit'),(34,'00000094',1,'2025-02-23','05:53:47 AM','Actualizar personal','El usuario Jeison12345 actualizo los datos del personal con cedula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(35,'00000285',1,'2025-02-23','05:53:47 AM','Actualizar empleado','El usuario Jeison12345 actualizo los datos del empleado con cedula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(36,'00000704',1,'2025-02-23','05:53:47 AM','Registrar documento','El usuario Jeison12345 ha colocado un nuevo documento en el sistema llamado Captura_de_pantalla_2025-02-17_232457-30012937.png con el código: TSaB9X y un tamaño de: 303.19 KB',NULL,'::1','Chrome','Windows 10','64-bit'),(37,'00000786',1,'2025-02-23','07:14:32 AM','Actualizar personal','El usuario Jeison12345 actualizo los datos del personal con cedula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(38,'00000542',1,'2025-02-23','07:14:32 AM','Actualizar empleado','El usuario Jeison12345 actualizo los datos del empleado con cedula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(39,'00000476',1,'2025-02-23','07:18:06 AM','Actualizar personal','El usuario Jeison12345 actualizo los datos del personal Jeison Balduz con cedula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(40,'00000265',1,'2025-02-23','07:18:06 AM','Actualizar empleado','El usuario Jeison12345 actualizo los datos del empleado con cedula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(41,'00000336',1,'2025-02-23','07:28:55 AM','Actualizar personal','El usuario Jeison12345 actualizo los datos del personal Jeison Balduz por tador de la cédula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(42,'00000176',1,'2025-02-23','07:28:55 AM','Actualizar empleado','El usuario Jeison12345 actualizo los datos del empleado con cedula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(43,'00000981',1,'2025-02-23','07:42:38 AM','Actualizar personal','El usuario Jeison12345 actualizo los datos del personal Jeison Balduz por tador de la cédula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(44,'00000704',1,'2025-02-23','07:42:38 AM','Actualizar empleado','El usuario Jeison12345 actualizo los datos del empleado con cedula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(45,'00000090',1,'2025-02-23','07:48:33 AM','Actualizar personal','El usuario Jeison12345 actualizo los datos del personal Jeison Balduz por tador de la cédula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(46,'00000508',1,'2025-02-23','07:48:33 AM','Actualizar empleado','El usuario Jeison12345 actualizo los datos del empleado con cedula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(47,'00000678',1,'2025-02-23','07:48:41 AM','Actualizar personal','El usuario Jeison12345 actualizo los datos del personal con cedula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(48,'00000950',1,'2025-02-23','07:48:41 AM','Actualizar empleado','El usuario Jeison12345 actualizo los datos del empleado con cedula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(49,'00000198',1,'2025-02-23','07:48:41 AM','Registrar documento','El usuario Jeison12345 ha colocado un nuevo documento en el sistema llamado Captura_de_pantalla_2025-02-17_234729-30012937.png con el código: RROdP3 y un tamaño de: 250.64 KB',NULL,'::1','Chrome','Windows 10','64-bit'),(50,'00000923',1,'2025-02-23','08:14:36 AM','Registrar documento','El usuario Jeison12345 ha colocado un nuevo documento en el sistema llamado Captura_de_pantalla_2025-02-17_232457-7456888.png con el código: usZ1SU y un tamaño de: 303.19 KB',NULL,'::1','Chrome','Windows 10','64-bit'),(51,'00000095',1,'2025-02-23','08:14:37 AM','Actualizar familiar','El usuario Jeison12345 Se actualizo los datos del familiarPedro Gonzalez asociados al empleado Jeison Balduz.',NULL,'::1','Chrome','Windows 10','64-bit'),(52,'00000828',1,'2025-02-23','08:27:26 AM','Actualizar familiar','El usuario Jeison12345 Se actualizo los datos del familiar Pedro Gonzalez asociados al empleado Jeison Balduz.',NULL,'::1','Chrome','Windows 10','64-bit'),(53,'00000600',1,'2025-02-23','08:28:54 AM','Actualizar familiar','El usuario Jeison12345 Se actualizo los datos del familiar Pedro Gonzalez asociados al empleado Jeison Balduz.',NULL,'::1','Chrome','Windows 10','64-bit'),(54,'00000535',1,'2025-02-23','08:30:47 AM','Actualizar familiar','El usuario Jeison12345 actualizo los datos del familiar Pedro Gonzalez asociados al empleado Jeison Balduz.',NULL,'::1','Chrome','Windows 10','64-bit'),(55,'00000647',1,'2025-02-23','08:30:58 AM','Actualizar familiar','El usuario Jeison12345 actualizo los datos del familiar Pedro Gonzalez asociados al empleado Jeison Balduz.',NULL,'::1','Chrome','Windows 10','64-bit'),(56,'00000505',1,'2025-02-23','08:39:53 AM','Actualizar familiar','El usuario Jeison12345 actualizo los datos del familiar Pedro Gonzalez asociados al empleado Jeison Balduz.',NULL,'::1','Chrome','Windows 10','64-bit'),(57,'00000484',1,'2025-02-23','10:56:20 AM','Generacion de ficha técnica','El usuarioJeison12345 a generado la ficha técnica del empleado Jeison Balduz',NULL,'::1','Chrome','Windows 10','64-bit'),(58,'00000728',1,'2025-02-23','11:45:31 AM','Generacion de ficha técnica','El usuarioJeison12345 a generado la ficha técnica del empleado Jeison Balduz',NULL,'::1','Chrome','Windows 10','64-bit'),(59,'00000118',1,'2025-02-23','12:23:48 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(60,'00000664',1,'2025-02-23','12:23:48 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(61,'00000236',1,'2025-02-23','12:23:48 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(62,'00000462',1,'2025-02-23','12:23:48 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(63,'00000471',1,'2025-02-23','12:23:48 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(64,'00000997',1,'2025-02-23','12:23:48 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(65,'00000818',1,'2025-02-23','13:02:52 PM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(66,'00000016',1,'2025-02-23','14:07:47 PM','Registrar Ausencia','Se registro una ausencia justificada para el empleado con cedula 30012937',NULL,'::1','Chrome','Windows 10','64-bit'),(67,'00000291',1,'2025-02-23','19:20:52 PM','Actualizar Ausencia','El usuario Jeison12345 actualizo una ausencia justificada para el empleado Jeison Balduz con cedula 30012937',NULL,'::1','Chrome','Windows 10','64-bit'),(68,'00000513',1,'2025-02-23','19:21:40 PM','Actualizar Ausencia','El usuario Jeison12345 actualizo una ausencia justificada para el empleado Jeison Balduz con cedula 30012937',NULL,'::1','Chrome','Windows 10','64-bit'),(69,'00000900',1,'2025-02-23','19:21:41 PM','Actualizar Ausencia','El usuario Jeison12345 actualizo una ausencia justificada para el empleado Jeison Balduz con cedula 30012937',NULL,'::1','Chrome','Windows 10','64-bit'),(70,'00000254',1,'2025-02-23','19:22:17 PM','Actualizar Ausencia','El usuario Jeison12345 actualizo una ausencia justificada para el empleado Jeison Balduz con cedula 30012937',NULL,'::1','Chrome','Windows 10','64-bit'),(71,'00000250',1,'2025-02-23','19:23:09 PM','Actualizar Ausencia','El usuario Jeison12345 actualizo una ausencia justificada para el empleado Jeison Balduz con cedula 30012937',NULL,'::1','Chrome','Windows 10','64-bit'),(72,'00000074',1,'2025-02-23','19:30:43 PM','Liberar Ausencia','El usuario Jeison12345 libero una ausencia justificada para el empleado   con cedula ',NULL,'::1','Chrome','Windows 10','64-bit'),(73,'00000508',1,'2025-02-23','19:33:05 PM','Liberar Ausencia','El usuario Jeison12345 libero una ausencia justificada para el empleado Jeison Balduz con cedula 30012937',NULL,'::1','Chrome','Windows 10','64-bit'),(74,'00000100',1,'2025-02-23','19:41:31 PM','Liberar Ausencia','El usuario Jeison12345 libero una ausencia justificada para el empleado Jeison Balduz con cedula 30012937',NULL,'::1','Chrome','Windows 10','64-bit'),(75,'00000954',1,'2025-02-23','19:41:33 PM','Registrar Ausencia','El usuario Jeison12345 registro una ausencia justificada para el empleado Jeison Balduz con cedula 30012937',NULL,'::1','Chrome','Windows 10','64-bit'),(76,'00000612',1,'2025-02-23','20:29:09 PM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(77,'00000652',1,'2025-02-23','20:30:20 PM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(78,'00000959',1,'2025-02-23','20:32:23 PM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(79,'00000560',1,'2025-02-23','20:37:30 PM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(80,'00000664',1,'2025-02-23','20:40:59 PM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(81,'00000259',1,'2025-02-23','20:53:00 PM','Liberar Ausencia','El usuario Jeison12345 libero una ausencia justificada para el empleado Jeison Balduz con cedula 30012937',NULL,'::1','Chrome','Windows 10','64-bit'),(82,'00000878',1,'2025-02-23','20:53:18 PM','Liberar Ausencia','El usuario Jeison12345 libero una ausencia justificada para el empleado Jeison Balduz con cedula 30012937',NULL,'::1','Chrome','Windows 10','64-bit'),(83,'00000808',1,'2025-02-23','20:53:55 PM','Registrar Ausencia','El usuario Jeison12345 registro una ausencia justificada para el empleado Pedro Barques con cedula 3285899',NULL,'::1','Chrome','Windows 10','64-bit'),(84,'00000480',1,'2025-02-23','20:56:54 PM','Registrar Ausencia','El usuario Jeison12345 registro una ausencia justificada para el empleado Pedro Barques con cedula 3285899',NULL,'::1','Chrome','Windows 10','64-bit'),(85,'00000445',1,'2025-02-23','21:21:47 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(86,'00000353',1,'2025-02-23','21:21:47 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(87,'00000811',1,'2025-02-23','21:21:47 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(88,'00000317',1,'2025-02-23','21:21:47 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(89,'00000935',1,'2025-02-23','21:21:47 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(90,'00000317',1,'2025-02-23','21:21:47 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(91,'00000469',1,'2025-02-23','21:33:08 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(92,'00000174',1,'2025-02-23','21:33:08 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(93,'00000820',1,'2025-02-23','21:33:08 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(94,'00000069',1,'2025-02-23','21:33:08 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(95,'00000865',1,'2025-02-23','21:33:08 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(96,'00000709',1,'2025-02-23','21:33:08 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(97,'00000544',1,'2025-02-23','21:33:18 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(98,'00000556',1,'2025-02-23','21:33:54 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(99,'00000150',1,'2025-02-23','21:34:51 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(100,'00000933',1,'2025-02-23','21:35:31 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(101,'00000888',1,'2025-02-23','21:37:13 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(102,'00000078',1,'2025-02-23','21:38:04 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(103,'00000547',1,'2025-02-23','21:39:37 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(104,'00000006',1,'2025-02-23','21:40:19 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(105,'00000404',1,'2025-02-23','21:43:14 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(106,'00000719',1,'2025-02-23','21:44:23 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(107,'00000586',1,'2025-02-23','21:44:44 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(108,'00000402',1,'2025-02-23','21:45:02 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(109,'00000278',1,'2025-02-23','21:45:19 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(110,'00000466',1,'2025-02-23','21:45:59 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(111,'00000864',1,'2025-02-23','21:46:57 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(112,'00000546',1,'2025-02-23','21:47:26 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(113,'00000797',1,'2025-02-23','21:50:46 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(114,'00000940',1,'2025-02-23','21:51:12 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(115,'00000792',1,'2025-02-23','21:51:26 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(116,'00000901',1,'2025-02-23','21:51:42 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(117,'00000478',1,'2025-02-23','22:39:26 PM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(118,'00000092',1,'2025-02-23','23:36:01 PM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(119,'00000190',1,'2025-02-24','06:27:06 AM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(120,'00000935',11,'2025-02-24','17:52:54 PM','Inicio de sesion','el usuario Emili123 a iniciado sesion en el sistema',NULL,'192.168.123.124','Chrome','Windows 10','64-bit'),(121,'00000285',1,'2025-02-25','00:24:37 AM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(122,'00000362',1,'2025-02-25','00:25:17 AM','Descargar ficha técnica','El usuarioJeison12345 a generado la ficha técnica del empleado Jeison Balduz',NULL,'::1','Chrome','Windows 10','64-bit'),(123,'00000337',1,'2025-02-25','08:31:13 AM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(124,'00000181',1,'2025-02-25','11:50:19 AM','cierre de sesion','el usuario Jeison12345 a cerrado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(125,'00000352',1,'2025-02-25','11:57:11 AM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(126,'00000294',1,'2025-02-25','11:59:03 AM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(127,'00000232',1,'2025-02-25','12:00:00 PM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(128,'00000550',1,'2025-02-25','21:22:06 PM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(129,'00000530',1,'2025-02-25','22:01:02 PM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(130,'00000443',1,'2025-02-26','00:58:17 AM','Registrar Vacaciones','El usuario Jeison12345 registro vacaciones para el empleado Jeison Balduz',NULL,'::1','Chrome','Windows 10','64-bit'),(131,'00000929',1,'2025-02-26','01:00:16 AM','Registrar Vacaciones','El usuario Jeison12345 registro vacaciones para el empleado Jeison Balduz',NULL,'::1','Chrome','Windows 10','64-bit'),(132,'00000819',1,'2025-02-26','01:30:41 AM','Registrar Vacaciones','El usuario Jeison12345 registro vacaciones para el empleado Jeison Balduz',NULL,'::1','Chrome','Windows 10','64-bit'),(133,'00000863',1,'2025-02-26','01:31:58 AM','Registrar Vacaciones','El usuario Jeison12345 registro vacaciones para el empleado Jeison Balduz',NULL,'::1','Chrome','Windows 10','64-bit'),(134,'00000979',1,'2025-02-26','03:45:22 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz',NULL,'::1','Chrome','Windows 10','64-bit'),(135,'00000556',1,'2025-02-26','03:45:26 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz',NULL,'::1','Chrome','Windows 10','64-bit'),(136,'00000191',1,'2025-02-26','03:45:29 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz',NULL,'::1','Chrome','Windows 10','64-bit'),(137,'00000373',1,'2025-02-26','03:45:32 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz',NULL,'::1','Chrome','Windows 10','64-bit'),(138,'00000867',1,'2025-02-26','03:45:53 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(139,'00000086',1,'2025-02-26','03:46:09 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(140,'00000256',1,'2025-02-26','03:46:11 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(141,'00000776',1,'2025-02-26','03:48:32 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(142,'00000163',1,'2025-02-26','03:50:17 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(143,'00000273',1,'2025-02-26','03:50:34 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(144,'00000901',1,'2025-02-26','03:54:18 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(145,'00000383',1,'2025-02-26','03:54:31 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(146,'00000151',1,'2025-02-26','03:56:43 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(147,'00000107',1,'2025-02-26','03:57:14 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(148,'00000569',1,'2025-02-26','04:08:05 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(149,'00000501',1,'2025-02-26','04:08:31 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(150,'00000041',1,'2025-02-26','04:11:10 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(151,'00000158',1,'2025-02-26','04:11:12 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(152,'00000993',1,'2025-02-26','04:13:40 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(153,'00000564',1,'2025-02-26','04:32:48 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(154,'00000660',1,'2025-02-26','04:33:52 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(155,'00000795',1,'2025-02-26','04:35:10 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(156,'00000861',1,'2025-02-26','04:37:46 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(157,'00000549',1,'2025-02-26','04:37:58 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(158,'00000659',1,'2025-02-26','04:40:00 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(159,'00000733',1,'2025-02-26','04:41:44 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(160,'00000108',1,'2025-02-26','04:42:55 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(161,'00000274',1,'2025-02-26','04:47:18 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(162,'00000012',1,'2025-02-26','04:48:15 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(163,'00000556',1,'2025-02-26','04:51:30 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(164,'00000944',1,'2025-02-26','04:54:33 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(165,'00000434',1,'2025-02-26','04:55:56 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(166,'00000759',1,'2025-02-26','05:05:11 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(167,'00000924',1,'2025-02-26','05:05:22 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(168,'00000095',1,'2025-02-26','05:06:00 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(169,'00000857',1,'2025-02-26','05:08:01 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(170,'00000727',1,'2025-02-26','05:19:38 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(171,'00000271',1,'2025-02-26','05:20:54 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(172,'00000021',1,'2025-02-26','05:21:10 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(173,'00000589',1,'2025-02-26','05:35:40 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(174,'00000699',1,'2025-02-26','05:38:45 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(175,'00000605',1,'2025-02-26','05:48:33 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(176,'00000177',1,'2025-02-26','05:50:21 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(177,'00000785',1,'2025-02-26','05:51:42 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(178,'00000970',1,'2025-02-26','05:53:48 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(179,'00000586',1,'2025-02-26','05:54:24 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(180,'00000362',1,'2025-02-26','05:54:34 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(181,'00000420',1,'2025-02-26','05:56:22 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(182,'00000282',1,'2025-02-26','05:56:29 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(183,'00000109',1,'2025-02-26','05:56:36 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(184,'00000050',1,'2025-02-26','05:56:41 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(185,'00000845',1,'2025-02-26','05:57:12 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz',NULL,'::1','Chrome','Windows 10','64-bit'),(186,'00000265',1,'2025-02-26','05:58:04 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(187,'00000262',1,'2025-02-26','05:58:09 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(188,'00000054',1,'2025-02-26','06:00:15 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(189,'00000471',1,'2025-02-26','06:00:42 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(190,'00000432',1,'2025-02-26','06:03:26 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(191,'00000926',1,'2025-02-26','06:05:46 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(192,'00000953',1,'2025-02-26','06:06:11 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz',NULL,'::1','Chrome','Windows 10','64-bit'),(193,'00000640',1,'2025-02-26','06:06:48 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(194,'00000469',1,'2025-02-26','06:06:57 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(195,'00000973',1,'2025-02-26','06:31:05 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(196,'00000186',1,'2025-02-26','06:31:58 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(197,'00000463',1,'2025-02-26','06:32:03 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(198,'00000860',1,'2025-02-26','07:23:04 AM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(199,'00000789',1,'2025-02-26','07:23:55 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(200,'00000676',1,'2025-02-26','07:24:09 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(201,'00000067',1,'2025-02-26','07:24:18 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(202,'00000665',1,'2025-02-26','07:30:36 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(203,'00000538',1,'2025-02-26','12:06:37 PM','Descargar ficha técnica','El usuarioJeison12345 a generado la ficha técnica del empleado Jeison Balduz',NULL,'::1','Chrome','Windows 10','64-bit'),(204,'00000217',1,'2025-02-26','19:44:41 PM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(205,'00000908',1,'2025-02-27','03:17:02 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(206,'00000092',1,'2025-02-27','03:17:15 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(207,'00000880',1,'2025-02-27','03:28:35 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(208,'00000015',1,'2025-02-27','03:28:43 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(209,'00000929',1,'2025-02-27','03:29:40 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(210,'00000160',1,'2025-02-27','03:30:30 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(211,'00000884',1,'2025-02-27','03:36:56 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(212,'00000909',1,'2025-02-27','03:37:04 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(213,'00000125',1,'2025-02-27','03:40:31 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(214,'00000307',1,'2025-02-27','03:46:53 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(215,'00000519',1,'2025-02-27','03:47:58 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(216,'00000817',1,'2025-02-27','03:51:30 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(217,'00000868',1,'2025-02-27','03:51:32 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres',NULL,'::1','Chrome','Windows 10','64-bit'),(218,'00000744',1,'2025-02-27','03:55:38 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de  a 3 y de  a 2015 año',NULL,'::1','Chrome','Windows 10','64-bit'),(219,'00000686',1,'2025-02-27','03:59:33 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de 3 a 3 y de 2015 a 2015 año',NULL,'::1','Chrome','Windows 10','64-bit'),(220,'00000447',1,'2025-02-27','04:02:36 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de los dias3 a 3 y de los años 2015 a 2016',NULL,'::1','Chrome','Windows 10','64-bit'),(221,'00000044',1,'2025-02-27','04:02:40 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de los dias3 a 3 y de los años 2016 a 2015',NULL,'::1','Chrome','Windows 10','64-bit'),(222,'00000103',1,'2025-02-27','04:02:46 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de los dias3 a 3 y de los años 2015 a 2016',NULL,'::1','Chrome','Windows 10','64-bit'),(223,'00000887',1,'2025-02-27','04:05:00 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de los dias1 a 3 y de los años 2015 a 2016',NULL,'::1','Chrome','Windows 10','64-bit'),(224,'00000038',1,'2025-02-27','04:05:04 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de los dias3 a 3 y de los años 2016 a 2015',NULL,'::1','Chrome','Windows 10','64-bit'),(225,'00000947',1,'2025-02-27','04:06:57 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de los dias1 a 3 y de los años 2015 a 2016',NULL,'::1','Chrome','Windows 10','64-bit'),(226,'00000652',1,'2025-02-27','04:06:57 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de los dias3 a 3 y de los años 2015 a 2016',NULL,'::1','Chrome','Windows 10','64-bit'),(227,'00000660',1,'2025-02-27','04:14:43 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de los dias3 a 3 y de los años 2016 a 2016',NULL,'::1','Chrome','Windows 10','64-bit'),(228,'00000858',1,'2025-02-27','04:14:50 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de los dias3 a 3 y de los años 2016 a 2016',NULL,'::1','Chrome','Windows 10','64-bit'),(229,'00000184',1,'2025-02-27','04:18:19 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de los dias3 a 3 y de los años 2016 a 2016',NULL,'::1','Chrome','Windows 10','64-bit'),(230,'00000626',1,'2025-02-27','04:31:25 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de los dias3 a 3 y de los años 2016 a 2017',NULL,'::1','Chrome','Windows 10','64-bit'),(231,'00000714',1,'2025-02-27','04:47:43 AM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(232,'00000070',1,'2025-02-27','08:40:19 AM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(233,'00000779',1,'2025-02-27','08:42:37 AM','Actualizar personal','El usuario Jeison12345 actualizo los datos del personal Jeison Balduz por tador de la cédula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(234,'00000803',1,'2025-02-27','08:42:37 AM','Actualizar empleado','El usuario Jeison12345 actualizo los datos del empleado con cedula 30012937.',NULL,'::1','Chrome','Windows 10','64-bit'),(235,'00000494',1,'2025-02-27','08:42:45 AM','Actualizar familiar','El usuario Jeison12345 actualizo los datos del familiar Pedro Gonzalez asociados al empleado Jeison Balduz.',NULL,'::1','Chrome','Windows 10','64-bit'),(236,'00000234',1,'2025-02-27','08:43:19 AM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(237,'00000329',1,'2025-02-27','08:45:35 AM','Descarga pdf de ausencia','El usuarioJeison12345 a descargado un pdf de los cargos en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(238,'00000137',1,'2025-02-27','09:00:43 AM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(239,'00000698',1,'2025-02-27','09:09:34 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de los dias3 a 3 y de los años 2017 a 2016',NULL,'::1','Chrome','Windows 10','64-bit'),(240,'00000579',1,'2025-02-27','09:53:16 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de los dias3 a 3 y de los años 2016 a 2017',NULL,'::1','Chrome','Windows 10','64-bit'),(241,'00000640',1,'2025-02-27','09:55:02 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de los dias3 a 1 y de los años 2017 a 2014',NULL,'::1','Chrome','Windows 10','64-bit'),(242,'00000206',1,'2025-02-27','09:56:47 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Emili Torres de los dias3 a 10 y de los años 2017 a 2015',NULL,'::1','Chrome','Windows 10','64-bit'),(243,'00000877',1,'2025-02-27','11:39:56 AM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(244,'00000045',1,'2025-02-27','11:40:08 AM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(245,'00000131',1,'2025-02-27','11:43:44 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz de los dias2 a 1 y de los años 2014 a 2014',NULL,'::1','Chrome','Windows 10','64-bit'),(246,'00000212',1,'2025-02-27','11:46:28 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz de los dias0 a 3 y de los años 2014 a 2014',NULL,'::1','Chrome','Windows 10','64-bit'),(247,'00000761',1,'2025-02-27','11:46:37 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz de los dias3 a 2 y de los años 2014 a 2014',NULL,'::1','Chrome','Windows 10','64-bit'),(248,'00000066',1,'2025-02-27','11:47:48 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz de los dias1 a 4 y de los años 2014 a 2014',NULL,'::1','Chrome','Windows 10','64-bit'),(249,'00000584',1,'2025-02-27','11:47:57 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz de los dias4 a 3 y de los años 2014 a 2014',NULL,'::1','Chrome','Windows 10','64-bit'),(250,'00000594',1,'2025-02-27','11:49:06 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz de los dias2 a 2 y de los años 2014 a 2014',NULL,'::1','Chrome','Windows 10','64-bit'),(251,'00000494',1,'2025-02-27','11:49:12 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz de los dias2 a 2 y de los años 2014 a 2014',NULL,'::1','Chrome','Windows 10','64-bit'),(252,'00000134',1,'2025-02-27','11:49:38 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz de los dias2 a 2 y de los años 2014 a 2014',NULL,'::1','Chrome','Windows 10','64-bit'),(253,'00000276',1,'2025-02-27','11:49:53 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz de los dias2 a 2 y de los años 2014 a 2014',NULL,'::1','Chrome','Windows 10','64-bit'),(254,'00000409',1,'2025-02-27','11:51:29 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz de los dias1 a 1 y de los años 2014 a 2014',NULL,'::1','Chrome','Windows 10','64-bit'),(255,'00000029',1,'2025-02-27','11:51:38 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz de los dias1 a 7 y de los años 2014 a 2014',NULL,'::1','Chrome','Windows 10','64-bit'),(256,'00000532',1,'2025-02-27','11:51:47 AM','Actualizar Vacaciones','El usuario Jeison12345 actualizo vacaciones para el empleado Jeison Balduz de los dias4 a 4 y de los años 2014 a 2014',NULL,'::1','Chrome','Windows 10','64-bit'),(257,'00000582',1,'2025-02-27','11:57:08 AM','Actualizar Vacaciones','El usuario Jeison12345 elimino las vacaciones para el empleado Jeison Balduz',NULL,'::1','Chrome','Windows 10','64-bit'),(258,'00000176',1,'2025-02-27','12:47:40 PM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(259,'00000308',1,'2025-02-27','13:12:09 PM','Descargar base de datos','El usuario Jeison12345 ha descargo la base de datos',NULL,'::1','Chrome','Windows 10','64-bit'),(260,'00000442',1,'2025-02-27','13:13:59 PM','cierre de sesion','el usuario Jeison12345 a cerrado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(261,'00000084',1,'2025-02-27','13:14:50 PM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(262,'00000722',1,'2025-02-27','13:27:43 PM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(263,'00000422',1,'2025-02-27','13:43:09 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(264,'00000648',1,'2025-02-27','13:43:09 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(265,'00000872',1,'2025-02-27','13:43:09 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(266,'00000592',1,'2025-02-27','13:43:09 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(267,'00000119',1,'2025-02-27','13:43:09 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(268,'00000705',1,'2025-02-27','13:43:09 PM','Descarga pdf de empleado','El usuarioJeison12345 a descargado un pdf de los empelados en tamaño carta formato vertical',NULL,'::1','Chrome','Windows 10','64-bit'),(269,'00000066',1,'2025-02-27','14:13:03 PM','cierre de sesion','el usuario Jeison12345 a cerrado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit'),(270,'00000495',1,'2025-02-27','14:13:49 PM','Inicio de sesion','el usuario Jeison12345 a iniciado sesion en el sistema',NULL,'::1','Chrome','Windows 10','64-bit');
/*!40000 ALTER TABLE `auditoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ausenciajustificada`
--

DROP TABLE IF EXISTS `ausenciajustificada`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ausenciajustificada` (
  `id_ausencia` int(11) NOT NULL AUTO_INCREMENT,
  `idEmpleado` int(11) NOT NULL,
  `idPermiso` varchar(50) NOT NULL,
  `fechaInicio` varchar(10) NOT NULL,
  `fechaFinal` varchar(10) NOT NULL,
  `activo` int(1) NOT NULL,
  `fecha` varchar(10) NOT NULL,
  `hora` varchar(20) NOT NULL,
  PRIMARY KEY (`id_ausencia`),
  KEY `empelado` (`idEmpleado`),
  CONSTRAINT `empelado` FOREIGN KEY (`idEmpleado`) REFERENCES `datosempleados` (`id_empleados`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ausenciajustificada`
--

LOCK TABLES `ausenciajustificada` WRITE;
/*!40000 ALTER TABLE `ausenciajustificada` DISABLE KEYS */;
INSERT INTO `ausenciajustificada` VALUES (36,57,'Descanso','2025-02-23','2025-02-25',1,'2025-02-23','20:56:54');
/*!40000 ALTER TABLE `ausenciajustificada` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `beneficios`
--

DROP TABLE IF EXISTS `beneficios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `beneficios` (
  `id_beneficio` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` text NOT NULL,
  `monto` float DEFAULT NULL,
  `lista` varchar(150) DEFAULT NULL,
  `fecha` varchar(10) NOT NULL,
  `hora` varchar(20) NOT NULL,
  PRIMARY KEY (`id_beneficio`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `beneficios`
--

LOCK TABLES `beneficios` WRITE;
/*!40000 ALTER TABLE `beneficios` DISABLE KEYS */;
INSERT INTO `beneficios` VALUES (1,'Fe de vi',NULL,NULL,'2024-01-01','09:00:00'),(2,'Botones',NULL,NULL,'2024-01-02','10:30:00'),(3,'Becas',NULL,NULL,'2024-01-03','12:00:00'),(4,'Kit escolares',NULL,NULL,'2024-01-04','14:15:00'),(5,'Día niños',NULL,NULL,'2024-01-05','16:30:00'),(6,'Plan vacacional',NULL,NULL,'2024-01-06','18:00:00'),(7,'Entre Kit',NULL,NULL,'2024-01-07','20:45:00'),(8,'Fiesta de fin de año',NULL,NULL,'2024-01-08','22:00:00'),(9,'Día del trabajador',NULL,NULL,'2024-01-09','08:30:00'),(10,'Día de la madre',NULL,NULL,'2024-01-10','11:00:00'),(11,'Dia del padre',NULL,NULL,'2024-01-11','13:45:00'),(12,'Día de la secretaria',NULL,NULL,'2024-01-12','15:30:00'),(13,'Ayuda funeraria',NULL,NULL,'2024-01-13','17:00:00'),(14,'Ayuda social al trabajador',NULL,NULL,'2024-01-14','19:15:00');
/*!40000 ALTER TABLE `beneficios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bitacorasistema`
--

DROP TABLE IF EXISTS `bitacorasistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bitacorasistema` (
  `id_bitacora` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL,
  `accion` varchar(250) NOT NULL,
  `cambio` varchar(250) NOT NULL,
  `direccionIP` varchar(15) NOT NULL,
  `´fecha` varchar(10) NOT NULL,
  `hora` varchar(20) NOT NULL,
  PRIMARY KEY (`id_bitacora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacorasistema`
--

LOCK TABLES `bitacorasistema` WRITE;
/*!40000 ALTER TABLE `bitacorasistema` DISABLE KEYS */;
/*!40000 ALTER TABLE `bitacorasistema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cargo`
--

DROP TABLE IF EXISTS `cargo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cargo` (
  `id_cargo` int(11) NOT NULL AUTO_INCREMENT,
  `cargo` varchar(50) NOT NULL,
  `pagoAsignado` decimal(10,0) NOT NULL,
  `activo` int(1) NOT NULL,
  PRIMARY KEY (`id_cargo`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cargo`
--

LOCK TABLES `cargo` WRITE;
/*!40000 ALTER TABLE `cargo` DISABLE KEYS */;
INSERT INTO `cargo` VALUES (1,'Auxiliar I',0,1),(2,'Auxiliar II',0,1),(3,'Auxiliar III',0,1),(4,'Analista I',0,0),(5,'Analista II',0,1),(6,'Analista III',0,1),(7,'Especialista I',0,1),(8,'Especialista II',0,1),(21,'Especialista III',0,1),(23,'Dsadsa',0,1),(24,'Pppaff',0,1);
/*!40000 ALTER TABLE `cargo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ciudades`
--

DROP TABLE IF EXISTS `ciudades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ciudades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estado_id` int(11) NOT NULL,
  `ciudad` varchar(200) NOT NULL,
  `capital` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_ciudades_1_idx` (`estado_id`),
  CONSTRAINT `fk_ciudades_1` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id_estado`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=523 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ciudades`
--

LOCK TABLES `ciudades` WRITE;
/*!40000 ALTER TABLE `ciudades` DISABLE KEYS */;
INSERT INTO `ciudades` VALUES (1,1,'Maroa',0),(2,1,'Puerto Ayacucho',1),(3,1,'San Fernando de Atabapo',0),(4,2,'Anaco',0),(5,2,'Aragua de Barcelona',0),(6,2,'Barcelona',1),(7,2,'Boca de Uchire',0),(8,2,'Cantaura',0),(9,2,'Clarines',0),(10,2,'El Chaparro',0),(11,2,'El Pao Anzoátegui',0),(12,2,'El Tigre',0),(13,2,'El Tigrito',0),(14,2,'Guanape',0),(15,2,'Guanta',0),(16,2,'Lechería',0),(17,2,'Onoto',0),(18,2,'Pariaguán',0),(19,2,'Píritu',0),(20,2,'Puerto La Cruz',0),(21,2,'Puerto Píritu',0),(22,2,'Sabana de Uchire',0),(23,2,'San Mateo Anzoátegui',0),(24,2,'San Pablo Anzoátegui',0),(25,2,'San Tomé',0),(26,2,'Santa Ana de Anzoátegui',0),(27,2,'Santa Fe Anzoátegui',0),(28,2,'Santa Rosa',0),(29,2,'Soledad',0),(30,2,'Urica',0),(31,2,'Valle de Guanape',0),(43,3,'Achaguas',0),(44,3,'Biruaca',0),(45,3,'Bruzual',0),(46,3,'El Amparo',0),(47,3,'El Nula',0),(48,3,'Elorza',0),(49,3,'Guasdualito',0),(50,3,'Mantecal',0),(51,3,'Puerto Páez',0),(52,3,'San Fernando de Apure',1),(53,3,'San Juan de Payara',0),(54,4,'Barbacoas',0),(55,4,'Cagua',0),(56,4,'Camatagua',0),(58,4,'Choroní',0),(59,4,'Colonia Tovar',0),(60,4,'El Consejo',0),(61,4,'La Victoria',0),(62,4,'Las Tejerías',0),(63,4,'Magdaleno',0),(64,4,'Maracay',1),(65,4,'Ocumare de La Costa',0),(66,4,'Palo Negro',0),(67,4,'San Casimiro',0),(68,4,'San Mateo',0),(69,4,'San Sebastián',0),(70,4,'Santa Cruz de Aragua',0),(71,4,'Tocorón',0),(72,4,'Turmero',0),(73,4,'Villa de Cura',0),(74,4,'Zuata',0),(75,5,'Barinas',1),(76,5,'Barinitas',0),(77,5,'Barrancas',0),(78,5,'Calderas',0),(79,5,'Capitanejo',0),(80,5,'Ciudad Bolivia',0),(81,5,'El Cantón',0),(82,5,'Las Veguitas',0),(83,5,'Libertad de Barinas',0),(84,5,'Sabaneta',0),(85,5,'Santa Bárbara de Barinas',0),(86,5,'Socopó',0),(87,6,'Caicara del Orinoco',0),(88,6,'Canaima',0),(89,6,'Ciudad Bolívar',1),(90,6,'Ciudad Piar',0),(91,6,'El Callao',0),(92,6,'El Dorado',0),(93,6,'El Manteco',0),(94,6,'El Palmar',0),(95,6,'El Pao',0),(96,6,'Guasipati',0),(97,6,'Guri',0),(98,6,'La Paragua',0),(99,6,'Matanzas',0),(100,6,'Puerto Ordaz',0),(101,6,'San Félix',0),(102,6,'Santa Elena de Uairén',0),(103,6,'Tumeremo',0),(104,6,'Unare',0),(105,6,'Upata',0),(106,7,'Bejuma',0),(107,7,'Belén',0),(108,7,'Campo de Carabobo',0),(109,7,'Canoabo',0),(110,7,'Central Tacarigua',0),(111,7,'Chirgua',0),(112,7,'Ciudad Alianza',0),(113,7,'El Palito',0),(114,7,'Guacara',0),(115,7,'Guigue',0),(116,7,'Las Trincheras',0),(117,7,'Los Guayos',0),(118,7,'Mariara',0),(119,7,'Miranda',0),(120,7,'Montalbán',0),(121,7,'Morón',0),(122,7,'Naguanagua',0),(123,7,'Puerto Cabello',0),(124,7,'San Joaquín',0),(125,7,'Tocuyito',0),(126,7,'Urama',0),(127,7,'Valencia',1),(128,7,'Vigirimita',0),(129,8,'Aguirre',0),(130,8,'Apartaderos Cojedes',0),(131,8,'Arismendi',0),(132,8,'Camuriquito',0),(133,8,'El Baúl',0),(134,8,'El Limón',0),(135,8,'El Pao Cojedes',0),(136,8,'El Socorro',0),(137,8,'La Aguadita',0),(138,8,'Las Vegas',0),(139,8,'Libertad de Cojedes',0),(140,8,'Mapuey',0),(141,8,'Piñedo',0),(142,8,'Samancito',0),(143,8,'San Carlos',1),(144,8,'Sucre',0),(145,8,'Tinaco',0),(146,8,'Tinaquillo',0),(147,8,'Vallecito',0),(148,9,'Tucupita',1),(149,24,'Caracas',1),(150,24,'El Junquito',0),(151,10,'Adícora',0),(152,10,'Boca de Aroa',0),(153,10,'Cabure',0),(154,10,'Capadare',0),(155,10,'Capatárida',0),(156,10,'Chichiriviche',0),(157,10,'Churuguara',0),(158,10,'Coro',1),(159,10,'Cumarebo',0),(160,10,'Dabajuro',0),(161,10,'Judibana',0),(162,10,'La Cruz de Taratara',0),(163,10,'La Vela de Coro',0),(164,10,'Los Taques',0),(165,10,'Maparari',0),(166,10,'Mene de Mauroa',0),(167,10,'Mirimire',0),(168,10,'Pedregal',0),(169,10,'Píritu Falcón',0),(170,10,'Pueblo Nuevo Falcón',0),(171,10,'Puerto Cumarebo',0),(172,10,'Punta Cardón',0),(173,10,'Punto Fijo',0),(174,10,'San Juan de Los Cayos',0),(175,10,'San Luis',0),(176,10,'Santa Ana Falcón',0),(177,10,'Santa Cruz De Bucaral',0),(178,10,'Tocopero',0),(179,10,'Tocuyo de La Costa',0),(180,10,'Tucacas',0),(181,10,'Yaracal',0),(182,11,'Altagracia de Orituco',0),(183,11,'Cabruta',0),(184,11,'Calabozo',0),(185,11,'Camaguán',0),(196,11,'Chaguaramas Guárico',0),(197,11,'El Socorro',0),(198,11,'El Sombrero',0),(199,11,'Las Mercedes de Los Llanos',0),(200,11,'Lezama',0),(201,11,'Onoto',0),(202,11,'Ortíz',0),(203,11,'San José de Guaribe',0),(204,11,'San Juan de Los Morros',1),(205,11,'San Rafael de Laya',0),(206,11,'Santa María de Ipire',0),(207,11,'Tucupido',0),(208,11,'Valle de La Pascua',0),(209,11,'Zaraza',0),(210,12,'Aguada Grande',0),(211,12,'Atarigua',0),(212,12,'Barquisimeto',1),(213,12,'Bobare',0),(214,12,'Cabudare',0),(215,12,'Carora',0),(216,12,'Cubiro',0),(217,12,'Cují',0),(218,12,'Duaca',0),(219,12,'El Manzano',0),(220,12,'El Tocuyo',0),(221,12,'Guaríco',0),(222,12,'Humocaro Alto',0),(223,12,'Humocaro Bajo',0),(224,12,'La Miel',0),(225,12,'Moroturo',0),(226,12,'Quíbor',0),(227,12,'Río Claro',0),(228,12,'Sanare',0),(229,12,'Santa Inés',0),(230,12,'Sarare',0),(231,12,'Siquisique',0),(232,12,'Tintorero',0),(233,13,'Apartaderos Mérida',0),(234,13,'Arapuey',0),(235,13,'Bailadores',0),(236,13,'Caja Seca',0),(237,13,'Canaguá',0),(238,13,'Chachopo',0),(239,13,'Chiguara',0),(240,13,'Ejido',0),(241,13,'El Vigía',0),(242,13,'La Azulita',0),(243,13,'La Playa',0),(244,13,'Lagunillas Mérida',0),(245,13,'Mérida',1),(246,13,'Mesa de Bolívar',0),(247,13,'Mucuchíes',0),(248,13,'Mucujepe',0),(249,13,'Mucuruba',0),(250,13,'Nueva Bolivia',0),(251,13,'Palmarito',0),(252,13,'Pueblo Llano',0),(253,13,'Santa Cruz de Mora',0),(254,13,'Santa Elena de Arenales',0),(255,13,'Santo Domingo',0),(256,13,'Tabáy',0),(257,13,'Timotes',0),(258,13,'Torondoy',0),(259,13,'Tovar',0),(260,13,'Tucani',0),(261,13,'Zea',0),(262,14,'Araguita',0),(263,14,'Carrizal',0),(264,14,'Caucagua',0),(265,14,'Chaguaramas Miranda',0),(266,14,'Charallave',0),(267,14,'Chirimena',0),(268,14,'Chuspa',0),(269,14,'Cúa',0),(270,14,'Cupira',0),(271,14,'Curiepe',0),(272,14,'El Guapo',0),(273,14,'El Jarillo',0),(274,14,'Filas de Mariche',0),(275,14,'Guarenas',0),(276,14,'Guatire',0),(277,14,'Higuerote',0),(278,14,'Los Anaucos',0),(279,14,'Los Teques',1),(280,14,'Ocumare del Tuy',0),(281,14,'Panaquire',0),(282,14,'Paracotos',0),(283,14,'Río Chico',0),(284,14,'San Antonio de Los Altos',0),(285,14,'San Diego de Los Altos',0),(286,14,'San Fernando del Guapo',0),(287,14,'San Francisco de Yare',0),(288,14,'San José de Los Altos',0),(289,14,'San José de Río Chico',0),(290,14,'San Pedro de Los Altos',0),(291,14,'Santa Lucía',0),(292,14,'Santa Teresa',0),(293,14,'Tacarigua de La Laguna',0),(294,14,'Tacarigua de Mamporal',0),(295,14,'Tácata',0),(296,14,'Turumo',0),(297,15,'Aguasay',0),(298,15,'Aragua de Maturín',0),(299,15,'Barrancas del Orinoco',0),(300,15,'Caicara de Maturín',0),(301,15,'Caripe',0),(302,15,'Caripito',0),(303,15,'Chaguaramal',0),(305,15,'Chaguaramas Monagas',0),(307,15,'El Furrial',0),(308,15,'El Tejero',0),(309,15,'Jusepín',0),(310,15,'La Toscana',0),(311,15,'Maturín',1),(312,15,'Miraflores',0),(313,15,'Punta de Mata',0),(314,15,'Quiriquire',0),(315,15,'San Antonio de Maturín',0),(316,15,'San Vicente Monagas',0),(317,15,'Santa Bárbara',0),(318,15,'Temblador',0),(319,15,'Teresen',0),(320,15,'Uracoa',0),(321,16,'Altagracia',0),(322,16,'Boca de Pozo',0),(323,16,'Boca de Río',0),(324,16,'El Espinal',0),(325,16,'El Valle del Espíritu Santo',0),(326,16,'El Yaque',0),(327,16,'Juangriego',0),(328,16,'La Asunción',1),(329,16,'La Guardia',0),(330,16,'Pampatar',0),(331,16,'Porlamar',0),(332,16,'Puerto Fermín',0),(333,16,'Punta de Piedras',0),(334,16,'San Francisco de Macanao',0),(335,16,'San Juan Bautista',0),(336,16,'San Pedro de Coche',0),(337,16,'Santa Ana de Nueva Esparta',0),(338,16,'Villa Rosa',0),(339,17,'Acarigua',0),(340,17,'Agua Blanca',0),(341,17,'Araure',0),(342,17,'Biscucuy',0),(343,17,'Boconoito',0),(344,17,'Campo Elías',0),(345,17,'Chabasquén',0),(346,17,'Guanare',1),(347,17,'Guanarito',0),(348,17,'La Aparición',0),(349,17,'La Misión',0),(350,17,'Mesa de Cavacas',0),(351,17,'Ospino',0),(352,17,'Papelón',0),(353,17,'Payara',0),(354,17,'Pimpinela',0),(355,17,'Píritu de Portuguesa',0),(356,17,'San Rafael de Onoto',0),(357,17,'Santa Rosalía',0),(358,17,'Turén',0),(359,18,'Altos de Sucre',0),(360,18,'Araya',0),(361,18,'Cariaco',0),(362,18,'Carúpano',0),(363,18,'Casanay',0),(364,18,'Cumaná',1),(365,18,'Cumanacoa',0),(366,18,'El Morro Puerto Santo',0),(367,18,'El Pilar',0),(368,18,'El Poblado',0),(369,18,'Guaca',0),(370,18,'Guiria',0),(371,18,'Irapa',0),(372,18,'Manicuare',0),(373,18,'Mariguitar',0),(374,18,'Río Caribe',0),(375,18,'San Antonio del Golfo',0),(376,18,'San José de Aerocuar',0),(377,18,'San Vicente de Sucre',0),(378,18,'Santa Fe de Sucre',0),(379,18,'Tunapuy',0),(380,18,'Yaguaraparo',0),(381,18,'Yoco',0),(382,19,'Abejales',0),(383,19,'Borota',0),(384,19,'Bramon',0),(385,19,'Capacho',0),(386,19,'Colón',0),(387,19,'Coloncito',0),(388,19,'Cordero',0),(389,19,'El Cobre',0),(390,19,'El Pinal',0),(391,19,'Independencia',0),(392,19,'La Fría',0),(393,19,'La Grita',0),(394,19,'La Pedrera',0),(395,19,'La Tendida',0),(396,19,'Las Delicias',0),(397,19,'Las Hernández',0),(398,19,'Lobatera',0),(399,19,'Michelena',0),(400,19,'Palmira',0),(401,19,'Pregonero',0),(402,19,'Queniquea',0),(403,19,'Rubio',0),(404,19,'San Antonio del Tachira',0),(405,19,'San Cristobal',1),(406,19,'San José de Bolívar',0),(407,19,'San Josecito',0),(408,19,'San Pedro del Río',0),(409,19,'Santa Ana Táchira',0),(410,19,'Seboruco',0),(411,19,'Táriba',0),(412,19,'Umuquena',0),(413,19,'Ureña',0),(414,20,'Batatal',0),(415,20,'Betijoque',0),(416,20,'Boconó',0),(417,20,'Carache',0),(418,20,'Chejende',0),(419,20,'Cuicas',0),(420,20,'El Dividive',0),(421,20,'El Jaguito',0),(422,20,'Escuque',0),(423,20,'Isnotú',0),(424,20,'Jajó',0),(425,20,'La Ceiba',0),(426,20,'La Concepción de Trujllo',0),(427,20,'La Mesa de Esnujaque',0),(428,20,'La Puerta',0),(429,20,'La Quebrada',0),(430,20,'Mendoza Fría',0),(431,20,'Meseta de Chimpire',0),(432,20,'Monay',0),(433,20,'Motatán',0),(434,20,'Pampán',0),(435,20,'Pampanito',0),(436,20,'Sabana de Mendoza',0),(437,20,'San Lázaro',0),(438,20,'Santa Ana de Trujillo',0),(439,20,'Tostós',0),(440,20,'Trujillo',1),(441,20,'Valera',0),(442,21,'Carayaca',0),(443,21,'Litoral',0),(444,25,'Archipiélago Los Roques',0),(445,22,'Aroa',0),(446,22,'Boraure',0),(447,22,'Campo Elías de Yaracuy',0),(448,22,'Chivacoa',0),(449,22,'Cocorote',0),(450,22,'Farriar',0),(451,22,'Guama',0),(452,22,'Marín',0),(453,22,'Nirgua',0),(454,22,'Sabana de Parra',0),(455,22,'Salom',0),(456,22,'San Felipe',1),(457,22,'San Pablo de Yaracuy',0),(458,22,'Urachiche',0),(459,22,'Yaritagua',0),(460,22,'Yumare',0),(461,23,'Bachaquero',0),(462,23,'Bobures',0),(463,23,'Cabimas',0),(464,23,'Campo Concepción',0),(465,23,'Campo Mara',0),(466,23,'Campo Rojo',0),(467,23,'Carrasquero',0),(468,23,'Casigua',0),(469,23,'Chiquinquirá',0),(470,23,'Ciudad Ojeda',0),(471,23,'El Batey',0),(472,23,'El Carmelo',0),(473,23,'El Chivo',0),(474,23,'El Guayabo',0),(475,23,'El Mene',0),(476,23,'El Venado',0),(477,23,'Encontrados',0),(478,23,'Gibraltar',0),(479,23,'Isla de Toas',0),(480,23,'La Concepción del Zulia',0),(481,23,'La Paz',0),(482,23,'La Sierrita',0),(483,23,'Lagunillas del Zulia',0),(484,23,'Las Piedras de Perijá',0),(485,23,'Los Cortijos',0),(486,23,'Machiques',0),(487,23,'Maracaibo',1),(488,23,'Mene Grande',0),(489,23,'Palmarejo',0),(490,23,'Paraguaipoa',0),(491,23,'Potrerito',0),(492,23,'Pueblo Nuevo del Zulia',0),(493,23,'Puertos de Altagracia',0),(494,23,'Punta Gorda',0),(495,23,'Sabaneta de Palma',0),(496,23,'San Francisco',0),(497,23,'San José de Perijá',0),(498,23,'San Rafael del Moján',0),(499,23,'San Timoteo',0),(500,23,'Santa Bárbara Del Zulia',0),(501,23,'Santa Cruz de Mara',0),(502,23,'Santa Cruz del Zulia',0),(503,23,'Santa Rita',0),(504,23,'Sinamaica',0),(505,23,'Tamare',0),(506,23,'Tía Juana',0),(507,23,'Villa del Rosario',0),(508,21,'La Guaira',1),(509,21,'Catia La Mar',0),(510,21,'Macuto',0),(511,21,'Naiguatá',0),(512,25,'Archipiélago Los Monjes',0),(513,25,'Isla La Tortuga y Cayos adyacentes',0),(514,25,'Isla La Sola',0),(515,25,'Islas Los Testigos',0),(516,25,'Islas Los Frailes',0),(517,25,'Isla La Orchila',0),(518,25,'Archipiélago Las Aves',0),(519,25,'Isla de Aves',0),(520,25,'Isla La Blanquilla',0),(521,25,'Isla de Patos',0),(522,25,'Islas Los Hermanos',0);
/*!40000 ALTER TABLE `ciudades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `datosempleados`
--

DROP TABLE IF EXISTS `datosempleados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datosempleados` (
  `id_empleados` int(11) NOT NULL AUTO_INCREMENT,
  `idPersonal` int(11) NOT NULL,
  `idEstatus` int(11) NOT NULL,
  `idCargo` int(11) NOT NULL,
  `idDependencia` int(11) NOT NULL,
  `idDepartamento` int(11) NOT NULL,
  `nivelAcademico` varchar(50) NOT NULL,
  `idGrp` int(11) DEFAULT NULL,
  `telefono` varchar(12) NOT NULL,
  `telOficina` varchar(12) DEFAULT NULL,
  `fechaING` varchar(15) DEFAULT NULL,
  `estadoEmpleado` varchar(100) DEFAULT NULL,
  `activo` int(1) NOT NULL,
  `fecha` varchar(10) NOT NULL,
  `hora` varchar(20) NOT NULL,
  PRIMARY KEY (`id_empleados`),
  KEY `idPersonal` (`idPersonal`),
  KEY `idEstatus` (`idEstatus`),
  KEY `idCargo` (`idCargo`),
  KEY `idDependencia` (`idDependencia`),
  KEY `idDepartamento` (`idDepartamento`),
  KEY `idGrp` (`idGrp`),
  CONSTRAINT `cargo` FOREIGN KEY (`idCargo`) REFERENCES `cargo` (`id_cargo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `datosempleados_ibfk_1` FOREIGN KEY (`idPersonal`) REFERENCES `datospersonales` (`id_personal`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `departamento` FOREIGN KEY (`idDepartamento`) REFERENCES `departamento` (`id_departamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dependencia` FOREIGN KEY (`idDependencia`) REFERENCES `dependencia` (`id_dependencia`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `estatus` FOREIGN KEY (`idEstatus`) REFERENCES `estatus` (`id_estatus`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datosempleados`
--

LOCK TABLES `datosempleados` WRITE;
/*!40000 ALTER TABLE `datosempleados` DISABLE KEYS */;
INSERT INTO `datosempleados` VALUES (1,1,1,8,11,1,'Maestria',1,' 04128977094','0243','2025-01-20','Jubilado',1,'2025-02-27','08:42:37 AM'),(57,111,10,4,12,1,'Ingeniero',NULL,'21321233121',NULL,NULL,NULL,0,'2024-12-06','06:39:15'),(73,149,1,1,40,1,'Bachiller',NULL,'04243406780',NULL,NULL,NULL,1,'2025-01-16','00:58:02'),(77,153,3,2,5,1,'Pregrado',NULL,'0412-8977095',NULL,NULL,NULL,1,'2025-01-20','12:07:56'),(78,154,5,3,3,3,'tecnologo',NULL,'0412-2131232',NULL,NULL,NULL,1,'2025-02-11','20:51:12'),(80,156,2,5,4,4,'ingeniero',NULL,'0412-2312312',NULL,NULL,NULL,1,'2025-02-23','04:29:44');
/*!40000 ALTER TABLE `datosempleados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `datosfamilia`
--

DROP TABLE IF EXISTS `datosfamilia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datosfamilia` (
  `id_ninos` int(11) NOT NULL AUTO_INCREMENT,
  `idEmpleado` int(11) NOT NULL,
  `primerNombre` text NOT NULL,
  `segundoNombre` text NOT NULL,
  `primerApellido` text NOT NULL,
  `segundoApellido` text NOT NULL,
  `parentesco` varchar(20) NOT NULL,
  `cedula` varchar(12) DEFAULT NULL,
  `codigoCarnet` varchar(20) DEFAULT NULL,
  `tomo` varchar(20) DEFAULT NULL,
  `folio` varchar(20) DEFAULT NULL,
  `edad` int(2) NOT NULL,
  `anoNacimiento` varchar(4) NOT NULL,
  `mesNacimiento` varchar(2) NOT NULL,
  `diaNacimiento` varchar(2) NOT NULL,
  `tallaFranela` int(2) DEFAULT NULL,
  `tallaPantalon` int(2) DEFAULT NULL,
  `activo` int(1) NOT NULL,
  `fecha` varchar(10) NOT NULL,
  `hora` varchar(20) NOT NULL,
  PRIMARY KEY (`id_ninos`),
  KEY `idEmpleado` (`idEmpleado`),
  CONSTRAINT `datosfamilia_ibfk_1` FOREIGN KEY (`idEmpleado`) REFERENCES `datosempleados` (`id_empleados`)
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datosfamilia`
--

LOCK TABLES `datosfamilia` WRITE;
/*!40000 ALTER TABLE `datosfamilia` DISABLE KEYS */;
INSERT INTO `datosfamilia` VALUES (157,1,'Pedro','Juan','Gonzalez','Torres','Hijo','7456888',NULL,NULL,NULL,10,'2018','04','01',NULL,NULL,1,'2025-02-27','08:42:45 AM'),(158,73,'Daniel','Jose','Ramirez','Santana','Hijo','12567890',NULL,NULL,NULL,17,'2010','02','12',NULL,NULL,1,'2025-01-16','13:13:44'),(159,1,'Elio','Samuel','Rodri','Torres','Hijo','14234567',NULL,NULL,NULL,7,'2003','12','21',NULL,NULL,1,'2025-01-16','14:14:08'),(166,57,'Eiker','Rodrigues','Pedro','Juan','Hija',NULL,'123123','12','768',14,'2010','07','16',NULL,NULL,1,'2025-01-25','10:23:02'),(171,1,'Weqeq','Qweqew','Qweqew','Qweqew','Madre','12321312',NULL,NULL,NULL,13,'2011','07','15',NULL,NULL,0,'2025-02-11','23:39:29'),(172,80,'Adsads','Adasd','Adasdas','Dsadsasd','Hija','213213131',NULL,NULL,NULL,12,'2012','07','12',NULL,NULL,1,'2025-02-23','04:57:33');
/*!40000 ALTER TABLE `datosfamilia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `datosninos`
--

DROP TABLE IF EXISTS `datosninos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datosninos` (
  `id_datosnino` int(11) NOT NULL AUTO_INCREMENT,
  `idNino` int(11) NOT NULL,
  `certificadoEstudio` varchar(250) DEFAULT NULL,
  `certificadoNotas` varchar(250) DEFAULT NULL,
  `partidaNacimiento` varchar(250) DEFAULT NULL,
  `certificadoMedico` varchar(250) DEFAULT NULL,
  `certificadoDisca` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id_datosnino`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datosninos`
--

LOCK TABLES `datosninos` WRITE;
/*!40000 ALTER TABLE `datosninos` DISABLE KEYS */;
/*!40000 ALTER TABLE `datosninos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `datospersonales`
--

DROP TABLE IF EXISTS `datospersonales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datospersonales` (
  `id_personal` int(11) NOT NULL AUTO_INCREMENT,
  `primerNombre` text NOT NULL,
  `segundoNombre` text NOT NULL,
  `primerApellido` text NOT NULL,
  `segundoApellido` text NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `estadoCivil` varchar(30) DEFAULT NULL,
  `firma` varchar(150) DEFAULT NULL,
  `foto` varchar(150) DEFAULT NULL,
  `diaNacimiento` varchar(2) NOT NULL,
  `mesNacimiento` varchar(2) NOT NULL,
  `anoNacimiento` varchar(4) NOT NULL,
  `correo` varchar(200) DEFAULT NULL,
  `fecha` varchar(10) NOT NULL,
  `hora` varchar(20) NOT NULL,
  PRIMARY KEY (`id_personal`)
) ENGINE=InnoDB AUTO_INCREMENT=167 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datospersonales`
--

LOCK TABLES `datospersonales` WRITE;
/*!40000 ALTER TABLE `datospersonales` DISABLE KEYS */;
INSERT INTO `datospersonales` VALUES (1,'Jeison','Andres','Balduz','Gonzalezzz','30012937','',NULL,NULL,'01','11','2014','','2025-02-27',' 08:42:37 AM'),(111,'Pedro','Gregorio','Barques','Jose','3285899','Casado','1',NULL,'15','09','2009',NULL,'2024-12-06','06:39:15'),(149,'Emili','Gabriela','Torres','Girand','30197049','Soltero',NULL,NULL,'31','05','2003',NULL,'2025-01-16','00:58:02'),(153,'Juan','Gonzalez','Andres','Balduz','3886615','Casado',NULL,NULL,'16','12','2008',NULL,'2025-01-20','12:07:55'),(154,'Jose','Torres','Juan','Corre','6127503','soltero',NULL,NULL,'13','10','2011',NULL,'2025-02-11','20:51:12'),(156,'Juan','Dsadsa','kevin','Gomez','1018683','soltero',NULL,NULL,'17','07','2010',NULL,'2025-02-23','04:29:44');
/*!40000 ALTER TABLE `datospersonales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departamento`
--

DROP TABLE IF EXISTS `departamento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departamento` (
  `id_departamento` int(11) NOT NULL AUTO_INCREMENT,
  `departamento` varchar(100) NOT NULL,
  `codigoDepa` varchar(50) DEFAULT NULL,
  `activo` int(1) NOT NULL,
  PRIMARY KEY (`id_departamento`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departamento`
--

LOCK TABLES `departamento` WRITE;
/*!40000 ALTER TABLE `departamento` DISABLE KEYS */;
INSERT INTO `departamento` VALUES (1,'División de Informática',NULL,1),(2,'Planificación',NULL,1),(3,'Cultura',NULL,1),(4,'Deporte',NULL,1),(5,'Prensa',NULL,1),(6,'Gerencia Regional',NULL,1),(7,'Asesoría Legal',NULL,1),(8,'Encadenamiento Productivo',NULL,1),(10,'Servicio y Mantenimiento',NULL,1),(11,'Administración',NULL,1),(12,'Compras',NULL,1),(13,'Bienes Nacionales',NULL,1),(14,'Almacén',NULL,1),(15,'Talento Humano',NULL,1),(16,'Servicio Médico',NULL,1),(17,'Formación Profesional',NULL,1),(18,'Currículo y Didáctico',NULL,1),(19,'Formación Delegado',NULL,1),(20,'Adiestramiento',NULL,1),(21,'PNA',NULL,1),(22,'Liceo INCES Aragua',NULL,1),(23,'Turismo',NULL,1),(24,'Móviles',NULL,1),(25,'Ferroviario',NULL,1),(26,'Penitenciario Luisa Cáceres de Arismendi',NULL,1),(27,'Planificación',NULL,1),(29,'Deporte',NULL,1),(30,'Prensa',NULL,1),(31,'Gerencia Regional',NULL,1),(32,'Asesoría Legal',NULL,1),(33,'Encadenamiento Productivo',NULL,1),(34,'División de Informática',NULL,1),(35,'Servicio y mantenimiento',NULL,1),(36,'Administración',NULL,1),(37,'Compras',NULL,1),(38,'Bienes Nacionales',NULL,1),(39,'Almacén',NULL,1),(40,'Talento humano',NULL,1),(41,'Servicio Médico',NULL,1),(43,'Currículo y didáctico',NULL,1),(44,'Formación Delegada',NULL,1),(45,'Adiestramiento',NULL,1),(46,'ANP',NULL,1),(47,'Liceo INCES Aragua',NULL,1),(49,'Móviles',NULL,1),(51,'Penitenciario Luisa Cáceres de Arismendi',NULL,0);
/*!40000 ALTER TABLE `departamento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dependencia`
--

DROP TABLE IF EXISTS `dependencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dependencia` (
  `id_dependencia` int(11) NOT NULL AUTO_INCREMENT,
  `dependencia` text NOT NULL,
  `idEstado` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `activo` int(1) NOT NULL,
  PRIMARY KEY (`id_dependencia`),
  KEY `idEstado` (`idEstado`),
  CONSTRAINT `estado_fk` FOREIGN KEY (`idEstado`) REFERENCES `estados` (`id_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dependencia`
--

LOCK TABLES `dependencia` WRITE;
/*!40000 ALTER TABLE `dependencia` DISABLE KEYS */;
INSERT INTO `dependencia` VALUES (1,'Coordinación Programa Turismo',4,'447304021',1),(2,'Coordinación Programa Penitenciario',4,'447306021',1),(3,'Centro Formación Comercial La Victoria',4,'18',1),(4,'Centro Formación Comercial Maracay',4,'19',1),(5,'Centro Formación Industrial El Limón',4,'20',1),(6,'Centro Polivalente Villa De Cura',4,'21',1),(7,'Centro De Formación Textil',4,'22',1),(8,'Centro Tecnologico Industrial La Victoria',4,'23',1),(9,'Centro Tecnológico Industrial Maracay',4,'24',1),(10,'Centro De Formación Construcción',4,'25',1),(11,'Centro Nacional De Mecánica Automotriz',4,'26',1),(12,'Centro Polivalente Bermudez',4,'27',1),(13,'Centro Polivalente Cagua',4,'28',1),(14,'Centro Polivalente Ocumare De La Costa',4,'29',1),(15,'C, F, S, A, La Providencia',4,'30',1),(16,'C, F, S, A, Colonia Tovar',4,'31',1),(17,'C, F, S, A, La Morita',4,'794',1),(18,'C,F,S Construcción',4,'931',1),(19,'C, F, S, Metal Minero La Victoria',4,'372',1),(20,'C, F, S, Cema',4,'771',1),(21,'C, F, S, Ocumare',4,'837',1),(22,'C, F, S, Maracay',4,'873',1),(23,'C, F, S, Textil',4,'850',1),(24,'C, F, S, Bermudez',4,'634',1),(25,'C, F, S, Cagua',4,'519',1),(26,'C, F, S, Comercial La Victoria',4,'594',1),(27,'C, F, S, El Limón',4,'413',1),(28,'C, F, S, Metalminero Maracay',4,'182',1),(29,'C, F, S, Programa Turismo',4,'474',1),(30,'C, F, S, Villa Cura',4,'822',1),(31,'CCFPI',4,'789',1),(32,'Cema ',4,'476',1),(33,'División De Administración',4,'4',1),(34,'División De Sercio Y Mantenimineto ',4,'5',1),(35,'División De Informatica',4,'6',1),(36,'División De Formacion Profesional',4,'7',1),(37,'División De Seguridad',4,'8',1),(38,'División De Talento Humano ',4,'3',1),(39,'Sede La Romana',4,'817',1),(40,'Sede Regional Aragua ',4,'754',1),(41,'Tributos Aragua',4,'319',1),(42,'Unidad De Planificación',4,'2',1),(43,'Unidad De Tecnología Educativa',4,'9',1),(44,'Unidad De Adiestramiento De Empresa',4,'13',1),(45,'Unidad De Formación Delegada',4,'14',1),(46,'Unidad Programa Navional Aprendisaje',4,'15',1),(47,'Unidades Móviles',4,'16',1),(48,'Coordinación Programa Ferroviario',4,'33',1),(52,'Jeison Pro Plus Max',1,'21312313',1),(64,'Emiliee',1,'132132112',1),(65,'Ewqeqe',2,'21313213',1);
/*!40000 ALTER TABLE `dependencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documentacion`
--

DROP TABLE IF EXISTS `documentacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documentacion` (
  `id_doc` int(11) NOT NULL AUTO_INCREMENT,
  `idEmpleados` int(11) NOT NULL,
  `idNinos` int(11) DEFAULT NULL,
  `tipoDoc` varchar(50) DEFAULT NULL,
  `nombreDoc` varchar(150) DEFAULT NULL,
  `size` varchar(80) DEFAULT NULL,
  `doc` varchar(250) DEFAULT NULL,
  `codigoArc` varchar(10) DEFAULT NULL,
  `fecha` varchar(10) NOT NULL,
  `hora` varchar(20) NOT NULL,
  `activo` int(1) NOT NULL,
  PRIMARY KEY (`id_doc`),
  KEY `idEmpleados` (`idEmpleados`),
  KEY `idNinos` (`idNinos`),
  CONSTRAINT `documentacion_ibfk_1` FOREIGN KEY (`idEmpleados`) REFERENCES `datosempleados` (`id_empleados`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=212 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documentacion`
--

LOCK TABLES `documentacion` WRITE;
/*!40000 ALTER TABLE `documentacion` DISABLE KEYS */;
INSERT INTO `documentacion` VALUES (62,1,57,'pdf',NULL,'500,00','Bootstrap_4_tutorial-30012937.pdf','edae','2025-01-10','15:05:01',1),(148,1,57,'png',NULL,'500,00','Logo_SICONTS1-3897645.png','EdaaT','2025-01-10','21:47:44',0),(149,1,57,'png',NULL,'500,00','Logo_SICONTS1-.png','aeqw45','2025-01-10','21:49:42',0),(150,1,NULL,'png',NULL,'500,00','Logo_SICONTS1-.png',NULL,'2025-01-10','21:50:10',0),(151,1,NULL,'png',NULL,'500,00','Logo_SICONTS1-3897645.png',NULL,'2025-01-10','21:54:40',0),(152,1,NULL,'png',NULL,'500,00','Captura-3897645.png',NULL,'2025-01-10','21:54:40',0),(153,1,NULL,'png',NULL,'500,00','Logo_SICONTS1-3897645.png',NULL,'2025-01-10','21:57:48',0),(154,1,NULL,'png',NULL,'500,00','Captura-3897645.png',NULL,'2025-01-10','21:57:48',0),(155,1,NULL,'png',NULL,'500,00','Logo_SICONTS1-3897645.png',NULL,'2025-01-10','22:03:55',0),(156,1,NULL,'png',NULL,'500,00','Captura-3897645.png',NULL,'2025-01-10','22:03:55',0),(163,73,NULL,'pdf',NULL,'500,00','CLEMENTE_MENDOZA_COT.44-30197049.pdf',NULL,'2025-01-16','00:58:02',0),(164,73,NULL,'pdf',NULL,'500,00','clemente_050-30197049.pdf',NULL,'2025-01-16','00:58:02',0),(165,73,NULL,'pdf',NULL,'500,00','JOSE_COPAS_046-433243212.pdf',NULL,'2025-01-16','02:00:25',0),(166,73,NULL,'pdf',NULL,'500,00','JOSE_COPAS_046-321313213.pdf',NULL,'2025-01-16','02:13:44',0),(167,73,NULL,'pdf',NULL,'500,00','JOSE_COPAS_046-321313213.pdf',NULL,'2025-01-16','02:17:44',0),(168,73,144,'pdf',NULL,'500,00','clemente_050-12345678.pdf',NULL,'2025-01-16','02:20:15',0),(171,1,156,'pdf',NULL,'500,00','JOSE_COPAS_046-3213891.pdf',NULL,'2025-01-16','03:20:54',0),(172,1,157,'pdf',NULL,'500,00','clemente_050-7456789.pdf',NULL,'2025-01-16','11:00:22',0),(173,1,NULL,'jpg',NULL,'500,00','IMG_20240923_120039-30012937.jpg',NULL,'2025-01-16','11:32:20',0),(174,1,NULL,'jpg',NULL,'500,00','FB_IMG_1725990635603-30012937.jpg',NULL,'2025-01-16','11:42:47',0),(175,1,NULL,'jpg',NULL,'500,00','Screenshot_20240509_074246_edit_9728005732039-30012937.jpg',NULL,'2025-01-16','11:42:47',0),(176,1,NULL,'jpg',NULL,'500,00','FB_IMG_1715723057779_(2)-30012937.jpg',NULL,'2025-01-16','11:48:06',0),(177,1,NULL,'jpg',NULL,'500,00','FB_IMG_1715723057779-30012937.jpg',NULL,'2025-01-16','11:50:23',0),(178,1,NULL,'jpg',NULL,'500,00','Screenshot_20240509_074334_edit_9752830653349-30012937.jpg',NULL,'2025-01-16','11:50:23',0),(179,1,NULL,'pdf',NULL,'500,00','rif_jeison-30012937.pdf',NULL,'2025-01-16','12:00:55',0),(180,1,NULL,'pdf',NULL,'500,00','factura_de_shein_leydi-30012937.pdf',NULL,'2025-01-16','12:00:55',0),(185,73,158,'pdf',NULL,'500,00','rif_jeison-12567890.pdf',NULL,'2025-01-16','13:13:44',0),(186,57,159,'pdf',NULL,'500,00','BingoElio-14234567.pdf',NULL,'2025-01-16','14:14:08',0),(189,77,NULL,'png',NULL,'500,00','Captura-30012935.png',NULL,'2025-01-20','12:07:56',0),(190,77,NULL,'png',NULL,'500,00','Logo_SICONTS1-30012935.png',NULL,'2025-01-20','12:07:56',0),(191,1,160,'png',NULL,'500,00','Logo_SICONTS1-21312312.png',NULL,'2025-01-21','13:15:45',0),(192,1,161,'png',NULL,'500,00','Logo_SICONTS1-21312312.png',NULL,'2025-01-21','13:17:55',0),(193,1,162,'png',NULL,'500,00','folders-123213123.png',NULL,'2025-01-21','13:19:47',0),(194,1,NULL,'jpg',NULL,'500,00','760ae84439a000216ffdf6e64177ad3d_1722410697792_1.webp-30012937.jpg',NULL,'2025-02-23','01:54:07',0),(195,1,NULL,'pdf',NULL,'500,00','diploma-php-30012937.pdf',NULL,'2025-02-23','08:57:05',0),(196,1,NULL,'pdf',NULL,'500,00','Documento_escaneado-30012937.pdf',NULL,'2025-02-23','08:57:18',1),(197,1,166,'png',NULL,'500,00','Captura-No Cédulado.png',NULL,'2025-02-25','10:23:02',1),(198,1,157,'jpg',NULL,'500,00','Captura_de_pantalla_2025-01-23_210055.PNG-7456789.jpg',NULL,'2025-02-25','20:51:52',1),(199,1,157,'png',NULL,'500,00','2537273db8991e0ee69e742d5c09ca96-7456789.png',NULL,'2025-02-25','22:02:43',0),(200,1,157,'png',NULL,'500,00','a1a146615845d22efa1e42e6689edc0f-7456999.png',NULL,'2025-02-25','22:03:17',0),(201,78,NULL,'pdf',NULL,'342.43 KB','CLEMENTE_MENDOZA_COT.44-213123123.pdf',NULL,'2025-02-11','20:51:12',0),(202,78,NULL,'jpg',NULL,'3,453.33 KB','cedula_xiomi-213123123.jpg',NULL,'2025-02-11','20:51:12',0),(203,1,171,'pdf',NULL,'141.17 KB','clemente_050-12321312.pdf',NULL,'2025-02-11','23:39:29',0),(206,80,NULL,'png',NULL,'63.05 KB','Captura_de_pantalla_2025-02-21_043751-1018683.png',NULL,'2025-02-23','04:29:44',0),(207,80,NULL,'png',NULL,'159.56 KB','Captura_de_pantalla_2025-02-22_194920-1018683.png',NULL,'2025-02-23','04:29:44',0),(208,80,172,'png',NULL,'250.64 KB','Captura_de_pantalla_2025-02-17_234729-213213131.png',NULL,'2025-02-23','04:57:33',0),(209,1,NULL,'png',NULL,'303.19 KB','Captura_de_pantalla_2025-02-17_232457-30012937.png',NULL,'2025-02-23','05:53:47',0),(210,1,NULL,'png',NULL,'250.64 KB','Captura_de_pantalla_2025-02-17_234729-30012937.png',NULL,'2025-02-23','07:48:41',0),(211,1,157,'png',NULL,'303.19 KB','Captura_de_pantalla_2025-02-17_232457-7456888.png',NULL,'2025-02-23','08:14:36',0);
/*!40000 ALTER TABLE `documentacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `egresos`
--

DROP TABLE IF EXISTS `egresos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `egresos` (
  `id_egreso` int(11) DEFAULT NULL,
  `fecha` varchar(10) NOT NULL,
  `hora` varchar(20) NOT NULL,
  `motivo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `egresos`
--

LOCK TABLES `egresos` WRITE;
/*!40000 ALTER TABLE `egresos` DISABLE KEYS */;
/*!40000 ALTER TABLE `egresos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estados`
--

DROP TABLE IF EXISTS `estados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estados` (
  `id_estado` int(11) NOT NULL AUTO_INCREMENT,
  `estado` varchar(250) NOT NULL,
  PRIMARY KEY (`id_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estados`
--

LOCK TABLES `estados` WRITE;
/*!40000 ALTER TABLE `estados` DISABLE KEYS */;
INSERT INTO `estados` VALUES (1,'Amazonas'),(2,'Anzoátegui'),(3,'Apure'),(4,'Aragua'),(5,'Barinas'),(6,'Bolívar'),(7,'Carabobo'),(8,'Cojedes'),(9,'Delta Amacuro'),(10,'Falcón'),(11,'Guárico'),(12,'Lara'),(13,'Mérida'),(14,'Miranda'),(15,'Monagas'),(16,'Nueva Esparta'),(17,'Portuguesa'),(18,'Sucre'),(19,'Táchira'),(20,'Trujillo'),(21,'Vargas'),(22,'Yaracuy'),(23,'Zulia'),(24,'Distrito Capital'),(25,'Dependencias Federales');
/*!40000 ALTER TABLE `estados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estatus`
--

DROP TABLE IF EXISTS `estatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estatus` (
  `id_estatus` int(11) NOT NULL AUTO_INCREMENT,
  `estatus` varchar(50) NOT NULL,
  `activo` int(1) NOT NULL,
  PRIMARY KEY (`id_estatus`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estatus`
--

LOCK TABLES `estatus` WRITE;
/*!40000 ALTER TABLE `estatus` DISABLE KEYS */;
INSERT INTO `estatus` VALUES (1,'Contradado',1),(2,'Cooperativa',1),(3,'Coral',1),(4,'Empleado',1),(5,'Estudiante',0),(6,'Jubilado',1),(7,'Maestro',0),(8,'Obrero',1),(9,'Pasante',1),(10,'Tributo',1),(11,'Vigilancia',1),(28,'Contratado',1);
/*!40000 ALTER TABLE `estatus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evento`
--

DROP TABLE IF EXISTS `evento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evento` (
  `id_evento` int(11) DEFAULT NULL,
  `idPersonal` int(11) DEFAULT NULL,
  `idNinos` int(11) DEFAULT NULL,
  `fiesta` varchar(250) NOT NULL,
  `motivo` varchar(250) DEFAULT NULL,
  `fecha` varchar(10) NOT NULL,
  `hora` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evento`
--

LOCK TABLES `evento` WRITE;
/*!40000 ALTER TABLE `evento` DISABLE KEYS */;
/*!40000 ALTER TABLE `evento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gradoprofesional`
--

DROP TABLE IF EXISTS `gradoprofesional`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gradoprofesional` (
  `id_grado` int(11) DEFAULT NULL,
  `formacion` varchar(40) DEFAULT NULL,
  `cursos` varchar(50) DEFAULT NULL,
  `diplomado` varchar(50) DEFAULT NULL,
  `certificado` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gradoprofesional`
--

LOCK TABLES `gradoprofesional` WRITE;
/*!40000 ALTER TABLE `gradoprofesional` DISABLE KEYS */;
/*!40000 ALTER TABLE `gradoprofesional` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historialmedico`
--

DROP TABLE IF EXISTS `historialmedico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historialmedico` (
  `id_histomedico` int(11) NOT NULL AUTO_INCREMENT,
  `idatencion` int(11) NOT NULL,
  `idMedicamentos` int(11) NOT NULL,
  `motivo` varchar(250) NOT NULL,
  `insidencia` varchar(150) NOT NULL,
  `diagnostico` varchar(250) NOT NULL,
  `antfermedades` varchar(150) NOT NULL,
  `fercronicas` varchar(150) NOT NULL,
  `medicamentosURG` varchar(150) NOT NULL,
  `cirugia` varchar(150) NOT NULL,
  `hospitalizacionP` varchar(150) NOT NULL,
  `alergias` varchar(150) NOT NULL,
  `fecha` varchar(10) NOT NULL,
  `hora` varchar(20) NOT NULL,
  PRIMARY KEY (`id_histomedico`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historialmedico`
--

LOCK TABLES `historialmedico` WRITE;
/*!40000 ALTER TABLE `historialmedico` DISABLE KEYS */;
/*!40000 ALTER TABLE `historialmedico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historialpsicologico`
--

DROP TABLE IF EXISTS `historialpsicologico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historialpsicologico` (
  `id_historialP` int(11) NOT NULL AUTO_INCREMENT,
  `idAtencion` int(11) NOT NULL,
  `idHistorial` int(11) NOT NULL,
  `motivo` text NOT NULL,
  `gravedad` text NOT NULL,
  `enfermedades` varchar(100) NOT NULL,
  `medicamentosURG` varchar(100) NOT NULL,
  `diagnostico` varchar(250) NOT NULL,
  `accidentes` varchar(100) NOT NULL,
  `historialFamiliar` varchar(250) NOT NULL,
  `exmanenMental` varchar(150) NOT NULL,
  `genitograma` varchar(150) NOT NULL,
  `observaciones` varchar(250) NOT NULL,
  `fecha` varchar(10) NOT NULL,
  `hora` varchar(20) NOT NULL,
  PRIMARY KEY (`id_historialP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historialpsicologico`
--

LOCK TABLES `historialpsicologico` WRITE;
/*!40000 ALTER TABLE `historialpsicologico` DISABLE KEYS */;
/*!40000 ALTER TABLE `historialpsicologico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingresos`
--

DROP TABLE IF EXISTS `ingresos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ingresos` (
  `id_ingresos` int(11) DEFAULT NULL,
  `primerNombre` text NOT NULL,
  `segundoNombre` text NOT NULL,
  `primerApellido` text NOT NULL,
  `segundoApellido` text NOT NULL,
  `documentos` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingresos`
--

LOCK TABLES `ingresos` WRITE;
/*!40000 ALTER TABLE `ingresos` DISABLE KEYS */;
/*!40000 ALTER TABLE `ingresos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medicamentos`
--

DROP TABLE IF EXISTS `medicamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medicamentos` (
  `id_medicamentos` int(11) NOT NULL AUTO_INCREMENT,
  `codigoBarra` varchar(13) NOT NULL,
  `nombre` text NOT NULL,
  `marca` varchar(100) NOT NULL,
  `laboratorio` varchar(100) NOT NULL,
  `presentacion` varchar(50) NOT NULL,
  `concentracion` varchar(50) NOT NULL,
  `disponibilidad` int(2) NOT NULL,
  `cantidad` int(6) NOT NULL,
  `fecha` varchar(10) NOT NULL,
  `hora` varchar(20) NOT NULL,
  PRIMARY KEY (`id_medicamentos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medicamentos`
--

LOCK TABLES `medicamentos` WRITE;
/*!40000 ALTER TABLE `medicamentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `medicamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `memo`
--

DROP TABLE IF EXISTS `memo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `memo` (
  `id_motivo` int(11) DEFAULT NULL,
  `idUsuario` int(11) NOT NULL,
  `tipoMotivo` varchar(100) NOT NULL,
  `motivo` varchar(150) NOT NULL,
  `fecha` varchar(10) NOT NULL,
  `hora` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `memo`
--

LOCK TABLES `memo` WRITE;
/*!40000 ALTER TABLE `memo` DISABLE KEYS */;
/*!40000 ALTER TABLE `memo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `municipios`
--

DROP TABLE IF EXISTS `municipios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `municipios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estado_id` int(11) NOT NULL,
  `municipio` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_municipios_1_idx` (`estado_id`),
  CONSTRAINT `fk_municipios_1` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id_estado`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=463 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `municipios`
--

LOCK TABLES `municipios` WRITE;
/*!40000 ALTER TABLE `municipios` DISABLE KEYS */;
INSERT INTO `municipios` VALUES (1,1,'Alto Orinoco'),(2,1,'Atabapo'),(3,1,'Atures'),(4,1,'Autana'),(5,1,'Manapiare'),(6,1,'Maroa'),(7,1,'Río Negro'),(8,2,'Anaco'),(9,2,'Aragua'),(10,2,'Manuel Ezequiel Bruzual'),(11,2,'Diego Bautista Urbaneja'),(12,2,'Fernando Peñalver'),(13,2,'Francisco Del Carmen Carvajal'),(14,2,'General Sir Arthur McGregor'),(15,2,'Guanta'),(16,2,'Independencia'),(17,2,'José Gregorio Monagas'),(18,2,'Juan Antonio Sotillo'),(19,2,'Juan Manuel Cajigal'),(20,2,'Libertad'),(21,2,'Francisco de Miranda'),(22,2,'Pedro María Freites'),(23,2,'Píritu'),(24,2,'San José de Guanipa'),(25,2,'San Juan de Capistrano'),(26,2,'Santa Ana'),(27,2,'Simón Bolívar'),(28,2,'Simón Rodríguez'),(29,3,'Achaguas'),(30,3,'Biruaca'),(31,3,'Muñóz'),(32,3,'Páez'),(33,3,'Pedro Camejo'),(34,3,'Rómulo Gallegos'),(35,3,'San Fernando'),(36,4,'Atanasio Girardot'),(37,4,'Bolívar'),(38,4,'Camatagua'),(39,4,'Francisco Linares Alcántara'),(40,4,'José Ángel Lamas'),(41,4,'José Félix Ribas'),(42,4,'José Rafael Revenga'),(43,4,'Libertador'),(44,4,'Mario Briceño Iragorry'),(45,4,'Ocumare de la Costa de Oro'),(46,4,'San Casimiro'),(47,4,'San Sebastián'),(48,4,'Santiago Mariño'),(49,4,'Santos Michelena'),(50,4,'Sucre'),(51,4,'Tovar'),(52,4,'Urdaneta'),(53,4,'Zamora'),(54,5,'Alberto Arvelo Torrealba'),(55,5,'Andrés Eloy Blanco'),(56,5,'Antonio José de Sucre'),(57,5,'Arismendi'),(58,5,'Barinas'),(59,5,'Bolívar'),(60,5,'Cruz Paredes'),(61,5,'Ezequiel Zamora'),(62,5,'Obispos'),(63,5,'Pedraza'),(64,5,'Rojas'),(65,5,'Sosa'),(66,6,'Caroní'),(67,6,'Cedeño'),(68,6,'El Callao'),(69,6,'Gran Sabana'),(70,6,'Heres'),(71,6,'Piar'),(72,6,'Angostura (Raúl Leoni)'),(73,6,'Roscio'),(74,6,'Sifontes'),(75,6,'Sucre'),(76,6,'Padre Pedro Chien'),(77,7,'Bejuma'),(78,7,'Carlos Arvelo'),(79,7,'Diego Ibarra'),(80,7,'Guacara'),(81,7,'Juan José Mora'),(82,7,'Libertador'),(83,7,'Los Guayos'),(84,7,'Miranda'),(85,7,'Montalbán'),(86,7,'Naguanagua'),(87,7,'Puerto Cabello'),(88,7,'San Diego'),(89,7,'San Joaquín'),(90,7,'Valencia'),(91,8,'Anzoátegui'),(92,8,'Tinaquillo'),(93,8,'Girardot'),(94,8,'Lima Blanco'),(95,8,'Pao de San Juan Bautista'),(96,8,'Ricaurte'),(97,8,'Rómulo Gallegos'),(98,8,'San Carlos'),(99,8,'Tinaco'),(100,9,'Antonio Díaz'),(101,9,'Casacoima'),(102,9,'Pedernales'),(103,9,'Tucupita'),(104,10,'Acosta'),(105,10,'Bolívar'),(106,10,'Buchivacoa'),(107,10,'Cacique Manaure'),(108,10,'Carirubana'),(109,10,'Colina'),(110,10,'Dabajuro'),(111,10,'Democracia'),(112,10,'Falcón'),(113,10,'Federación'),(114,10,'Jacura'),(115,10,'José Laurencio Silva'),(116,10,'Los Taques'),(117,10,'Mauroa'),(118,10,'Miranda'),(119,10,'Monseñor Iturriza'),(120,10,'Palmasola'),(121,10,'Petit'),(122,10,'Píritu'),(123,10,'San Francisco'),(124,10,'Sucre'),(125,10,'Tocópero'),(126,10,'Unión'),(127,10,'Urumaco'),(128,10,'Zamora'),(129,11,'Camaguán'),(130,11,'Chaguaramas'),(131,11,'El Socorro'),(132,11,'José Félix Ribas'),(133,11,'José Tadeo Monagas'),(134,11,'Juan Germán Roscio'),(135,11,'Julián Mellado'),(136,11,'Las Mercedes'),(137,11,'Leonardo Infante'),(138,11,'Pedro Zaraza'),(139,11,'Ortíz'),(140,11,'San Gerónimo de Guayabal'),(141,11,'San José de Guaribe'),(142,11,'Santa María de Ipire'),(143,11,'Sebastián Francisco de Miranda'),(144,12,'Andrés Eloy Blanco'),(145,12,'Crespo'),(146,12,'Iribarren'),(147,12,'Jiménez'),(148,12,'Morán'),(149,12,'Palavecino'),(150,12,'Simón Planas'),(151,12,'Torres'),(152,12,'Urdaneta'),(179,13,'Alberto Adriani'),(180,13,'Andrés Bello'),(181,13,'Antonio Pinto Salinas'),(182,13,'Aricagua'),(183,13,'Arzobispo Chacón'),(184,13,'Campo Elías'),(185,13,'Caracciolo Parra Olmedo'),(186,13,'Cardenal Quintero'),(187,13,'Guaraque'),(188,13,'Julio César Salas'),(189,13,'Justo Briceño'),(190,13,'Libertador'),(191,13,'Miranda'),(192,13,'Obispo Ramos de Lora'),(193,13,'Padre Noguera'),(194,13,'Pueblo Llano'),(195,13,'Rangel'),(196,13,'Rivas Dávila'),(197,13,'Santos Marquina'),(198,13,'Sucre'),(199,13,'Tovar'),(200,13,'Tulio Febres Cordero'),(201,13,'Zea'),(223,14,'Acevedo'),(224,14,'Andrés Bello'),(225,14,'Baruta'),(226,14,'Brión'),(227,14,'Buroz'),(228,14,'Carrizal'),(229,14,'Chacao'),(230,14,'Cristóbal Rojas'),(231,14,'El Hatillo'),(232,14,'Guaicaipuro'),(233,14,'Independencia'),(234,14,'Lander'),(235,14,'Los Salias'),(236,14,'Páez'),(237,14,'Paz Castillo'),(238,14,'Pedro Gual'),(239,14,'Plaza'),(240,14,'Simón Bolívar'),(241,14,'Sucre'),(242,14,'Urdaneta'),(243,14,'Zamora'),(258,15,'Acosta'),(259,15,'Aguasay'),(260,15,'Bolívar'),(261,15,'Caripe'),(262,15,'Cedeño'),(263,15,'Ezequiel Zamora'),(264,15,'Libertador'),(265,15,'Maturín'),(266,15,'Piar'),(267,15,'Punceres'),(268,15,'Santa Bárbara'),(269,15,'Sotillo'),(270,15,'Uracoa'),(271,16,'Antolín del Campo'),(272,16,'Arismendi'),(273,16,'García'),(274,16,'Gómez'),(275,16,'Maneiro'),(276,16,'Marcano'),(277,16,'Mariño'),(278,16,'Península de Macanao'),(279,16,'Tubores'),(280,16,'Villalba'),(281,16,'Díaz'),(282,17,'Agua Blanca'),(283,17,'Araure'),(284,17,'Esteller'),(285,17,'Guanare'),(286,17,'Guanarito'),(287,17,'Monseñor José Vicente de Unda'),(288,17,'Ospino'),(289,17,'Páez'),(290,17,'Papelón'),(291,17,'San Genaro de Boconoíto'),(292,17,'San Rafael de Onoto'),(293,17,'Santa Rosalía'),(294,17,'Sucre'),(295,17,'Turén'),(296,18,'Andrés Eloy Blanco'),(297,18,'Andrés Mata'),(298,18,'Arismendi'),(299,18,'Benítez'),(300,18,'Bermúdez'),(301,18,'Bolívar'),(302,18,'Cajigal'),(303,18,'Cruz Salmerón Acosta'),(304,18,'Libertador'),(305,18,'Mariño'),(306,18,'Mejía'),(307,18,'Montes'),(308,18,'Ribero'),(309,18,'Sucre'),(310,18,'Valdéz'),(341,19,'Andrés Bello'),(342,19,'Antonio Rómulo Costa'),(343,19,'Ayacucho'),(344,19,'Bolívar'),(345,19,'Cárdenas'),(346,19,'Córdoba'),(347,19,'Fernández Feo'),(348,19,'Francisco de Miranda'),(349,19,'García de Hevia'),(350,19,'Guásimos'),(351,19,'Independencia'),(352,19,'Jáuregui'),(353,19,'José María Vargas'),(354,19,'Junín'),(355,19,'Libertad'),(356,19,'Libertador'),(357,19,'Lobatera'),(358,19,'Michelena'),(359,19,'Panamericano'),(360,19,'Pedro María Ureña'),(361,19,'Rafael Urdaneta'),(362,19,'Samuel Darío Maldonado'),(363,19,'San Cristóbal'),(364,19,'Seboruco'),(365,19,'Simón Rodríguez'),(366,19,'Sucre'),(367,19,'Torbes'),(368,19,'Uribante'),(369,19,'San Judas Tadeo'),(370,20,'Andrés Bello'),(371,20,'Boconó'),(372,20,'Bolívar'),(373,20,'Candelaria'),(374,20,'Carache'),(375,20,'Escuque'),(376,20,'José Felipe Márquez Cañizalez'),(377,20,'Juan Vicente Campos Elías'),(378,20,'La Ceiba'),(379,20,'Miranda'),(380,20,'Monte Carmelo'),(381,20,'Motatán'),(382,20,'Pampán'),(383,20,'Pampanito'),(384,20,'Rafael Rangel'),(385,20,'San Rafael de Carvajal'),(386,20,'Sucre'),(387,20,'Trujillo'),(388,20,'Urdaneta'),(389,20,'Valera'),(390,21,'Vargas'),(391,22,'Arístides Bastidas'),(392,22,'Bolívar'),(407,22,'Bruzual'),(408,22,'Cocorote'),(409,22,'Independencia'),(410,22,'José Antonio Páez'),(411,22,'La Trinidad'),(412,22,'Manuel Monge'),(413,22,'Nirgua'),(414,22,'Peña'),(415,22,'San Felipe'),(416,22,'Sucre'),(417,22,'Urachiche'),(418,22,'José Joaquín Veroes'),(441,23,'Almirante Padilla'),(442,23,'Baralt'),(443,23,'Cabimas'),(444,23,'Catatumbo'),(445,23,'Colón'),(446,23,'Francisco Javier Pulgar'),(447,23,'Páez'),(448,23,'Jesús Enrique Losada'),(449,23,'Jesús María Semprún'),(450,23,'La Cañada de Urdaneta'),(451,23,'Lagunillas'),(452,23,'Machiques de Perijá'),(453,23,'Mara'),(454,23,'Maracaibo'),(455,23,'Miranda'),(456,23,'Rosario de Perijá'),(457,23,'San Francisco'),(458,23,'Santa Rita'),(459,23,'Simón Bolívar'),(460,23,'Sucre'),(461,23,'Valmore Rodríguez'),(462,24,'Libertador');
/*!40000 ALTER TABLE `municipios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificaciones` (
  `id_noti` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL,
  `userRol` int(11) NOT NULL,
  `userQuien` int(11) DEFAULT NULL,
  `notificacion` varchar(250) NOT NULL,
  `fecha` varchar(15) NOT NULL,
  `hora` varchar(10) NOT NULL,
  PRIMARY KEY (`id_noti`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` VALUES (1,0,1,NULL,'El usuario Jeison12345a descagadola ficha tecnica del cedulado30012937','2025-02-16','19:17:29'),(2,1,1,NULL,'El usuario  Jeison12345 a descagadola ficha tecnica del cedulado 30012937','2025-02-16','20:01:38'),(3,1,1,NULL,'El usuario  Jeison12345 a descagadola ficha tecnica del cedulado 30012937','2025-02-16','20:02:45'),(4,1,1,NULL,'El usuario  Jeison12345 a descagadola ficha tecnica del cedulado 30012937','2025-02-17','09:07:55'),(5,1,1,NULL,'El usuario  Jeison12345 a descagadola ficha tecnica del cedulado 30012937','2025-02-17','09:08:10'),(6,1,1,NULL,'El usuario  Jeison12345 a descagadola ficha tecnica del cedulado 30012937','2025-02-18','23:24:58'),(7,1,1,NULL,'El usuario  Jeison12345 a descagadola ficha tecnica del cedulado 30012937','2025-02-20','01:25:34'),(8,1,1,NULL,'El usuario  Jeison12345 a descagadola ficha tecnica del cedulado 30012937','2025-02-23','10:56:22'),(9,1,1,NULL,'El usuario  Jeison12345 a descagadola ficha tecnica del cedulado 30012937','2025-02-23','11:45:33'),(10,1,1,NULL,'El usuario  Jeison12345 a descagadola ficha tecnica del cedulado 30012937','2025-02-25','00:25:19'),(11,1,1,NULL,'El usuario  Jeison12345 a descagadola ficha tecnica del cedulado 30012937','2025-02-26','12:06:39 P');
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parroquias`
--

DROP TABLE IF EXISTS `parroquias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parroquias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `municipio_id` int(11) NOT NULL,
  `parroquia` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_parroquias_1_idx` (`municipio_id`),
  CONSTRAINT `fk_parroquias_1` FOREIGN KEY (`municipio_id`) REFERENCES `municipios` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1139 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parroquias`
--

LOCK TABLES `parroquias` WRITE;
/*!40000 ALTER TABLE `parroquias` DISABLE KEYS */;
INSERT INTO `parroquias` VALUES (1,1,'Alto Orinoco'),(2,1,'Huachamacare Acanaña'),(3,1,'Marawaka Toky Shamanaña'),(4,1,'Mavaka Mavaka'),(5,1,'Sierra Parima Parimabé'),(6,2,'Ucata Laja Lisa'),(7,2,'Yapacana Macuruco'),(8,2,'Caname Guarinuma'),(9,3,'Fernando Girón Tovar'),(10,3,'Luis Alberto Gómez'),(11,3,'Pahueña Limón de Parhueña'),(12,3,'Platanillal Platanillal'),(13,4,'Samariapo'),(14,4,'Sipapo'),(15,4,'Munduapo'),(16,4,'Guayapo'),(17,5,'Alto Ventuari'),(18,5,'Medio Ventuari'),(19,5,'Bajo Ventuari'),(20,6,'Victorino'),(21,6,'Comunidad'),(22,7,'Casiquiare'),(23,7,'Cocuy'),(24,7,'San Carlos de Río Negro'),(25,7,'Solano'),(26,8,'Anaco'),(27,8,'San Joaquín'),(28,9,'Cachipo'),(29,9,'Aragua de Barcelona'),(30,11,'Lechería'),(31,11,'El Morro'),(32,12,'Puerto Píritu'),(33,12,'San Miguel'),(34,12,'Sucre'),(35,13,'Valle de Guanape'),(36,13,'Santa Bárbara'),(37,14,'El Chaparro'),(38,14,'Tomás Alfaro'),(39,14,'Calatrava'),(40,15,'Guanta'),(41,15,'Chorrerón'),(42,16,'Mamo'),(43,16,'Soledad'),(44,17,'Mapire'),(45,17,'Piar'),(46,17,'Santa Clara'),(47,17,'San Diego de Cabrutica'),(48,17,'Uverito'),(49,17,'Zuata'),(50,18,'Puerto La Cruz'),(51,18,'Pozuelos'),(52,19,'Onoto'),(53,19,'San Pablo'),(54,20,'San Mateo'),(55,20,'El Carito'),(56,20,'Santa Inés'),(57,20,'La Romereña'),(58,21,'Atapirire'),(59,21,'Boca del Pao'),(60,21,'El Pao'),(61,21,'Pariaguán'),(62,22,'Cantaura'),(63,22,'Libertador'),(64,22,'Santa Rosa'),(65,22,'Urica'),(66,23,'Píritu'),(67,23,'San Francisco'),(68,24,'San José de Guanipa'),(69,25,'Boca de Uchire'),(70,25,'Boca de Chávez'),(71,26,'Pueblo Nuevo'),(72,26,'Santa Ana'),(73,27,'Bergatín'),(74,27,'Caigua'),(75,27,'El Carmen'),(76,27,'El Pilar'),(77,27,'Naricual'),(78,27,'San Crsitóbal'),(79,28,'Edmundo Barrios'),(80,28,'Miguel Otero Silva'),(81,29,'Achaguas'),(82,29,'Apurito'),(83,29,'El Yagual'),(84,29,'Guachara'),(85,29,'Mucuritas'),(86,29,'Queseras del medio'),(87,30,'Biruaca'),(88,31,'Bruzual'),(89,31,'Mantecal'),(90,31,'Quintero'),(91,31,'Rincón Hondo'),(92,31,'San Vicente'),(93,32,'Guasdualito'),(94,32,'Aramendi'),(95,32,'El Amparo'),(96,32,'San Camilo'),(97,32,'Urdaneta'),(98,33,'San Juan de Payara'),(99,33,'Codazzi'),(100,33,'Cunaviche'),(101,34,'Elorza'),(102,34,'La Trinidad'),(103,35,'San Fernando'),(104,35,'El Recreo'),(105,35,'Peñalver'),(106,35,'San Rafael de Atamaica'),(107,36,'Pedro José Ovalles'),(108,36,'Joaquín Crespo'),(109,36,'José Casanova Godoy'),(110,36,'Madre María de San José'),(111,36,'Andrés Eloy Blanco'),(112,36,'Los Tacarigua'),(113,36,'Las Delicias'),(114,36,'Choroní'),(115,37,'Bolívar'),(116,38,'Camatagua'),(117,38,'Carmen de Cura'),(118,39,'Santa Rita'),(119,39,'Francisco de Miranda'),(120,39,'Moseñor Feliciano González'),(121,40,'Santa Cruz'),(122,41,'José Félix Ribas'),(123,41,'Castor Nieves Ríos'),(124,41,'Las Guacamayas'),(125,41,'Pao de Zárate'),(126,41,'Zuata'),(127,42,'José Rafael Revenga'),(128,43,'Palo Negro'),(129,43,'San Martín de Porres'),(130,44,'El Limón'),(131,44,'Caña de Azúcar'),(132,45,'Ocumare de la Costa'),(133,46,'San Casimiro'),(134,46,'Güiripa'),(135,46,'Ollas de Caramacate'),(136,46,'Valle Morín'),(137,47,'San Sebastían'),(138,48,'Turmero'),(139,48,'Arevalo Aponte'),(140,48,'Chuao'),(141,48,'Samán de Güere'),(142,48,'Alfredo Pacheco Miranda'),(143,49,'Santos Michelena'),(144,49,'Tiara'),(145,50,'Cagua'),(146,50,'Bella Vista'),(147,51,'Tovar'),(148,52,'Urdaneta'),(149,52,'Las Peñitas'),(150,52,'San Francisco de Cara'),(151,52,'Taguay'),(152,53,'Zamora'),(153,53,'Magdaleno'),(154,53,'San Francisco de Asís'),(155,53,'Valles de Tucutunemo'),(156,53,'Augusto Mijares'),(157,54,'Sabaneta'),(158,54,'Juan Antonio Rodríguez Domínguez'),(159,55,'El Cantón'),(160,55,'Santa Cruz de Guacas'),(161,55,'Puerto Vivas'),(162,56,'Ticoporo'),(163,56,'Nicolás Pulido'),(164,56,'Andrés Bello'),(165,57,'Arismendi'),(166,57,'Guadarrama'),(167,57,'La Unión'),(168,57,'San Antonio'),(169,58,'Barinas'),(170,58,'Alberto Arvelo Larriva'),(171,58,'San Silvestre'),(172,58,'Santa Inés'),(173,58,'Santa Lucía'),(174,58,'Torumos'),(175,58,'El Carmen'),(176,58,'Rómulo Betancourt'),(177,58,'Corazón de Jesús'),(178,58,'Ramón Ignacio Méndez'),(179,58,'Alto Barinas'),(180,58,'Manuel Palacio Fajardo'),(181,58,'Juan Antonio Rodríguez Domínguez'),(182,58,'Dominga Ortiz de Páez'),(183,59,'Barinitas'),(184,59,'Altamira de Cáceres'),(185,59,'Calderas'),(186,60,'Barrancas'),(187,60,'El Socorro'),(188,60,'Mazparrito'),(189,61,'Santa Bárbara'),(190,61,'Pedro Briceño Méndez'),(191,61,'Ramón Ignacio Méndez'),(192,61,'José Ignacio del Pumar'),(193,62,'Obispos'),(194,62,'Guasimitos'),(195,62,'El Real'),(196,62,'La Luz'),(197,63,'Ciudad Bolívia'),(198,63,'José Ignacio Briceño'),(199,63,'José Félix Ribas'),(200,63,'Páez'),(201,64,'Libertad'),(202,64,'Dolores'),(203,64,'Santa Rosa'),(204,64,'Palacio Fajardo'),(205,65,'Ciudad de Nutrias'),(206,65,'El Regalo'),(207,65,'Puerto Nutrias'),(208,65,'Santa Catalina'),(209,66,'Cachamay'),(210,66,'Chirica'),(211,66,'Dalla Costa'),(212,66,'Once de Abril'),(213,66,'Simón Bolívar'),(214,66,'Unare'),(215,66,'Universidad'),(216,66,'Vista al Sol'),(217,66,'Pozo Verde'),(218,66,'Yocoima'),(219,66,'5 de Julio'),(220,67,'Cedeño'),(221,67,'Altagracia'),(222,67,'Ascensión Farreras'),(223,67,'Guaniamo'),(224,67,'La Urbana'),(225,67,'Pijiguaos'),(226,68,'El Callao'),(227,69,'Gran Sabana'),(228,69,'Ikabarú'),(229,70,'Catedral'),(230,70,'Zea'),(231,70,'Orinoco'),(232,70,'José Antonio Páez'),(233,70,'Marhuanta'),(234,70,'Agua Salada'),(235,70,'Vista Hermosa'),(236,70,'La Sabanita'),(237,70,'Panapana'),(238,71,'Andrés Eloy Blanco'),(239,71,'Pedro Cova'),(240,72,'Raúl Leoni'),(241,72,'Barceloneta'),(242,72,'Santa Bárbara'),(243,72,'San Francisco'),(244,73,'Roscio'),(245,73,'Salóm'),(246,74,'Sifontes'),(247,74,'Dalla Costa'),(248,74,'San Isidro'),(249,75,'Sucre'),(250,75,'Aripao'),(251,75,'Guarataro'),(252,75,'Las Majadas'),(253,75,'Moitaco'),(254,76,'Padre Pedro Chien'),(255,76,'Río Grande'),(256,77,'Bejuma'),(257,77,'Canoabo'),(258,77,'Simón Bolívar'),(259,78,'Güigüe'),(260,78,'Carabobo'),(261,78,'Tacarigua'),(262,79,'Mariara'),(263,79,'Aguas Calientes'),(264,80,'Ciudad Alianza'),(265,80,'Guacara'),(266,80,'Yagua'),(267,81,'Morón'),(268,81,'Yagua'),(269,82,'Tocuyito'),(270,82,'Independencia'),(271,83,'Los Guayos'),(272,84,'Miranda'),(273,85,'Montalbán'),(274,86,'Naguanagua'),(275,87,'Bartolomé Salóm'),(276,87,'Democracia'),(277,87,'Fraternidad'),(278,87,'Goaigoaza'),(279,87,'Juan José Flores'),(280,87,'Unión'),(281,87,'Borburata'),(282,87,'Patanemo'),(283,88,'San Diego'),(284,89,'San Joaquín'),(285,90,'Candelaria'),(286,90,'Catedral'),(287,90,'El Socorro'),(288,90,'Miguel Peña'),(289,90,'Rafael Urdaneta'),(290,90,'San Blas'),(291,90,'San José'),(292,90,'Santa Rosa'),(293,90,'Negro Primero'),(294,91,'Cojedes'),(295,91,'Juan de Mata Suárez'),(296,92,'Tinaquillo'),(297,93,'El Baúl'),(298,93,'Sucre'),(299,94,'La Aguadita'),(300,94,'Macapo'),(301,95,'El Pao'),(302,96,'El Amparo'),(303,96,'Libertad de Cojedes'),(304,97,'Rómulo Gallegos'),(305,98,'San Carlos de Austria'),(306,98,'Juan Ángel Bravo'),(307,98,'Manuel Manrique'),(308,99,'General en Jefe José Laurencio Silva'),(309,100,'Curiapo'),(310,100,'Almirante Luis Brión'),(311,100,'Francisco Aniceto Lugo'),(312,100,'Manuel Renaud'),(313,100,'Padre Barral'),(314,100,'Santos de Abelgas'),(315,101,'Imataca'),(316,101,'Cinco de Julio'),(317,101,'Juan Bautista Arismendi'),(318,101,'Manuel Piar'),(319,101,'Rómulo Gallegos'),(320,102,'Pedernales'),(321,102,'Luis Beltrán Prieto Figueroa'),(322,103,'San José (Delta Amacuro)'),(323,103,'José Vidal Marcano'),(324,103,'Juan Millán'),(325,103,'Leonardo Ruíz Pineda'),(326,103,'Mariscal Antonio José de Sucre'),(327,103,'Monseñor Argimiro García'),(328,103,'San Rafael (Delta Amacuro)'),(329,103,'Virgen del Valle'),(330,10,'Clarines'),(331,10,'Guanape'),(332,10,'Sabana de Uchire'),(333,104,'Capadare'),(334,104,'La Pastora'),(335,104,'Libertador'),(336,104,'San Juan de los Cayos'),(337,105,'Aracua'),(338,105,'La Peña'),(339,105,'San Luis'),(340,106,'Bariro'),(341,106,'Borojó'),(342,106,'Capatárida'),(343,106,'Guajiro'),(344,106,'Seque'),(345,106,'Zazárida'),(346,106,'Valle de Eroa'),(347,107,'Cacique Manaure'),(348,108,'Norte'),(349,108,'Carirubana'),(350,108,'Santa Ana'),(351,108,'Urbana Punta Cardón'),(352,109,'La Vela de Coro'),(353,109,'Acurigua'),(354,109,'Guaibacoa'),(355,109,'Las Calderas'),(356,109,'Macoruca'),(357,110,'Dabajuro'),(358,111,'Agua Clara'),(359,111,'Avaria'),(360,111,'Pedregal'),(361,111,'Piedra Grande'),(362,111,'Purureche'),(363,112,'Adaure'),(364,112,'Adícora'),(365,112,'Baraived'),(366,112,'Buena Vista'),(367,112,'Jadacaquiva'),(368,112,'El Vínculo'),(369,112,'El Hato'),(370,112,'Moruy'),(371,112,'Pueblo Nuevo'),(372,113,'Agua Larga'),(373,113,'El Paují'),(374,113,'Independencia'),(375,113,'Mapararí'),(376,114,'Agua Linda'),(377,114,'Araurima'),(378,114,'Jacura'),(379,115,'Tucacas'),(380,115,'Boca de Aroa'),(381,116,'Los Taques'),(382,116,'Judibana'),(383,117,'Mene de Mauroa'),(384,117,'San Félix'),(385,117,'Casigua'),(386,118,'Guzmán Guillermo'),(387,118,'Mitare'),(388,118,'Río Seco'),(389,118,'Sabaneta'),(390,118,'San Antonio'),(391,118,'San Gabriel'),(392,118,'Santa Ana'),(393,119,'Boca del Tocuyo'),(394,119,'Chichiriviche'),(395,119,'Tocuyo de la Costa'),(396,120,'Palmasola'),(397,121,'Cabure'),(398,121,'Colina'),(399,121,'Curimagua'),(400,122,'San José de la Costa'),(401,122,'Píritu'),(402,123,'San Francisco'),(403,124,'Sucre'),(404,124,'Pecaya'),(405,125,'Tocópero'),(406,126,'El Charal'),(407,126,'Las Vegas del Tuy'),(408,126,'Santa Cruz de Bucaral'),(409,127,'Bruzual'),(410,127,'Urumaco'),(411,128,'Puerto Cumarebo'),(412,128,'La Ciénaga'),(413,128,'La Soledad'),(414,128,'Pueblo Cumarebo'),(415,128,'Zazárida'),(416,113,'Churuguara'),(417,129,'Camaguán'),(418,129,'Puerto Miranda'),(419,129,'Uverito'),(420,130,'Chaguaramas'),(421,131,'El Socorro'),(422,132,'Tucupido'),(423,132,'San Rafael de Laya'),(424,133,'Altagracia de Orituco'),(425,133,'San Rafael de Orituco'),(426,133,'San Francisco Javier de Lezama'),(427,133,'Paso Real de Macaira'),(428,133,'Carlos Soublette'),(429,133,'San Francisco de Macaira'),(430,133,'Libertad de Orituco'),(431,134,'Cantaclaro'),(432,134,'San Juan de los Morros'),(433,134,'Parapara'),(434,135,'El Sombrero'),(435,135,'Sosa'),(436,136,'Las Mercedes'),(437,136,'Cabruta'),(438,136,'Santa Rita de Manapire'),(439,137,'Valle de la Pascua'),(440,137,'Espino'),(441,138,'San José de Unare'),(442,138,'Zaraza'),(443,139,'San José de Tiznados'),(444,139,'San Francisco de Tiznados'),(445,139,'San Lorenzo de Tiznados'),(446,139,'Ortiz'),(447,140,'Guayabal'),(448,140,'Cazorla'),(449,141,'San José de Guaribe'),(450,141,'Uveral'),(451,142,'Santa María de Ipire'),(452,142,'Altamira'),(453,143,'El Calvario'),(454,143,'El Rastro'),(455,143,'Guardatinajas'),(456,143,'Capital Urbana Calabozo'),(457,144,'Quebrada Honda de Guache'),(458,144,'Pío Tamayo'),(459,144,'Yacambú'),(460,145,'Fréitez'),(461,145,'José María Blanco'),(462,146,'Catedral'),(463,146,'Concepción'),(464,146,'El Cují'),(465,146,'Juan de Villegas'),(466,146,'Santa Rosa'),(467,146,'Tamaca'),(468,146,'Unión'),(469,146,'Aguedo Felipe Alvarado'),(470,146,'Buena Vista'),(471,146,'Juárez'),(472,147,'Juan Bautista Rodríguez'),(473,147,'Cuara'),(474,147,'Diego de Lozada'),(475,147,'Paraíso de San José'),(476,147,'San Miguel'),(477,147,'Tintorero'),(478,147,'José Bernardo Dorante'),(479,147,'Coronel Mariano Peraza '),(480,148,'Bolívar'),(481,148,'Anzoátegui'),(482,148,'Guarico'),(483,148,'Hilario Luna y Luna'),(484,148,'Humocaro Alto'),(485,148,'Humocaro Bajo'),(486,148,'La Candelaria'),(487,148,'Morán'),(488,149,'Cabudare'),(489,149,'José Gregorio Bastidas'),(490,149,'Agua Viva'),(491,150,'Sarare'),(492,150,'Buría'),(493,150,'Gustavo Vegas León'),(494,151,'Trinidad Samuel'),(495,151,'Antonio Díaz'),(496,151,'Camacaro'),(497,151,'Castañeda'),(498,151,'Cecilio Zubillaga'),(499,151,'Chiquinquirá'),(500,151,'El Blanco'),(501,151,'Espinoza de los Monteros'),(502,151,'Lara'),(503,151,'Las Mercedes'),(504,151,'Manuel Morillo'),(505,151,'Montaña Verde'),(506,151,'Montes de Oca'),(507,151,'Torres'),(508,151,'Heriberto Arroyo'),(509,151,'Reyes Vargas'),(510,151,'Altagracia'),(511,152,'Siquisique'),(512,152,'Moroturo'),(513,152,'San Miguel'),(514,152,'Xaguas'),(515,179,'Presidente Betancourt'),(516,179,'Presidente Páez'),(517,179,'Presidente Rómulo Gallegos'),(518,179,'Gabriel Picón González'),(519,179,'Héctor Amable Mora'),(520,179,'José Nucete Sardi'),(521,179,'Pulido Méndez'),(522,180,'La Azulita'),(523,181,'Santa Cruz de Mora'),(524,181,'Mesa Bolívar'),(525,181,'Mesa de Las Palmas'),(526,182,'Aricagua'),(527,182,'San Antonio'),(528,183,'Canagua'),(529,183,'Capurí'),(530,183,'Chacantá'),(531,183,'El Molino'),(532,183,'Guaimaral'),(533,183,'Mucutuy'),(534,183,'Mucuchachí'),(535,184,'Fernández Peña'),(536,184,'Matriz'),(537,184,'Montalbán'),(538,184,'Acequias'),(539,184,'Jají'),(540,184,'La Mesa'),(541,184,'San José del Sur'),(542,185,'Tucaní'),(543,185,'Florencio Ramírez'),(544,186,'Santo Domingo'),(545,186,'Las Piedras'),(546,187,'Guaraque'),(547,187,'Mesa de Quintero'),(548,187,'Río Negro'),(549,188,'Arapuey'),(550,188,'Palmira'),(551,189,'San Cristóbal de Torondoy'),(552,189,'Torondoy'),(553,190,'Antonio Spinetti Dini'),(554,190,'Arias'),(555,190,'Caracciolo Parra Pérez'),(556,190,'Domingo Peña'),(557,190,'El Llano'),(558,190,'Gonzalo Picón Febres'),(559,190,'Jacinto Plaza'),(560,190,'Juan Rodríguez Suárez'),(561,190,'Lasso de la Vega'),(562,190,'Mariano Picón Salas'),(563,190,'Milla'),(564,190,'Osuna Rodríguez'),(565,190,'Sagrario'),(566,190,'El Morro'),(567,190,'Los Nevados'),(568,191,'Andrés Eloy Blanco'),(569,191,'La Venta'),(570,191,'Piñango'),(571,191,'Timotes'),(572,192,'Eloy Paredes'),(573,192,'San Rafael de Alcázar'),(574,192,'Santa Elena de Arenales'),(575,193,'Santa María de Caparo'),(576,194,'Pueblo Llano'),(577,195,'Cacute'),(578,195,'La Toma'),(579,195,'Mucuchíes'),(580,195,'Mucurubá'),(581,195,'San Rafael'),(582,196,'Gerónimo Maldonado'),(583,196,'Bailadores'),(584,197,'Tabay'),(585,198,'Chiguará'),(586,198,'Estánquez'),(587,198,'Lagunillas'),(588,198,'La Trampa'),(589,198,'Pueblo Nuevo del Sur'),(590,198,'San Juan'),(591,199,'El Amparo'),(592,199,'El Llano'),(593,199,'San Francisco'),(594,199,'Tovar'),(595,200,'Independencia'),(596,200,'María de la Concepción Palacios Blanco'),(597,200,'Nueva Bolivia'),(598,200,'Santa Apolonia'),(599,201,'Caño El Tigre'),(600,201,'Zea'),(601,223,'Aragüita'),(602,223,'Arévalo González'),(603,223,'Capaya'),(604,223,'Caucagua'),(605,223,'Panaquire'),(606,223,'Ribas'),(607,223,'El Café'),(608,223,'Marizapa'),(609,224,'Cumbo'),(610,224,'San José de Barlovento'),(611,225,'El Cafetal'),(612,225,'Las Minas'),(613,225,'Nuestra Señora del Rosario'),(614,226,'Higuerote'),(615,226,'Curiepe'),(616,226,'Tacarigua de Brión'),(617,227,'Mamporal'),(618,228,'Carrizal'),(619,229,'Chacao'),(620,230,'Charallave'),(621,230,'Las Brisas'),(622,231,'El Hatillo'),(623,232,'Altagracia de la Montaña'),(624,232,'Cecilio Acosta'),(625,232,'Los Teques'),(626,232,'El Jarillo'),(627,232,'San Pedro'),(628,232,'Tácata'),(629,232,'Paracotos'),(630,233,'Cartanal'),(631,233,'Santa Teresa del Tuy'),(632,234,'La Democracia'),(633,234,'Ocumare del Tuy'),(634,234,'Santa Bárbara'),(635,235,'San Antonio de los Altos'),(636,236,'Río Chico'),(637,236,'El Guapo'),(638,236,'Tacarigua de la Laguna'),(639,236,'Paparo'),(640,236,'San Fernando del Guapo'),(641,237,'Santa Lucía del Tuy'),(642,238,'Cúpira'),(643,238,'Machurucuto'),(644,239,'Guarenas'),(645,240,'San Antonio de Yare'),(646,240,'San Francisco de Yare'),(647,241,'Leoncio Martínez'),(648,241,'Petare'),(649,241,'Caucagüita'),(650,241,'Filas de Mariche'),(651,241,'La Dolorita'),(652,242,'Cúa'),(653,242,'Nueva Cúa'),(654,243,'Guatire'),(655,243,'Bolívar'),(656,258,'San Antonio de Maturín'),(657,258,'San Francisco de Maturín'),(658,259,'Aguasay'),(659,260,'Caripito'),(660,261,'El Guácharo'),(661,261,'La Guanota'),(662,261,'Sabana de Piedra'),(663,261,'San Agustín'),(664,261,'Teresen'),(665,261,'Caripe'),(666,262,'Areo'),(667,262,'Capital Cedeño'),(668,262,'San Félix de Cantalicio'),(669,262,'Viento Fresco'),(670,263,'El Tejero'),(671,263,'Punta de Mata'),(672,264,'Chaguaramas'),(673,264,'Las Alhuacas'),(674,264,'Tabasca'),(675,264,'Temblador'),(676,265,'Alto de los Godos'),(677,265,'Boquerón'),(678,265,'Las Cocuizas'),(679,265,'La Cruz'),(680,265,'San Simón'),(681,265,'El Corozo'),(682,265,'El Furrial'),(683,265,'Jusepín'),(684,265,'La Pica'),(685,265,'San Vicente'),(686,266,'Aparicio'),(687,266,'Aragua de Maturín'),(688,266,'Chaguamal'),(689,266,'El Pinto'),(690,266,'Guanaguana'),(691,266,'La Toscana'),(692,266,'Taguaya'),(693,267,'Cachipo'),(694,267,'Quiriquire'),(695,268,'Santa Bárbara'),(696,269,'Barrancas'),(697,269,'Los Barrancos de Fajardo'),(698,270,'Uracoa'),(699,271,'Antolín del Campo'),(700,272,'Arismendi'),(701,273,'García'),(702,273,'Francisco Fajardo'),(703,274,'Bolívar'),(704,274,'Guevara'),(705,274,'Matasiete'),(706,274,'Santa Ana'),(707,274,'Sucre'),(708,275,'Aguirre'),(709,275,'Maneiro'),(710,276,'Adrián'),(711,276,'Juan Griego'),(712,276,'Yaguaraparo'),(713,277,'Porlamar'),(714,278,'San Francisco de Macanao'),(715,278,'Boca de Río'),(716,279,'Tubores'),(717,279,'Los Baleales'),(718,280,'Vicente Fuentes'),(719,280,'Villalba'),(720,281,'San Juan Bautista'),(721,281,'Zabala'),(722,283,'Capital Araure'),(723,283,'Río Acarigua'),(724,284,'Capital Esteller'),(725,284,'Uveral'),(726,285,'Guanare'),(727,285,'Córdoba'),(728,285,'San José de la Montaña'),(729,285,'San Juan de Guanaguanare'),(730,285,'Virgen de la Coromoto'),(731,286,'Guanarito'),(732,286,'Trinidad de la Capilla'),(733,286,'Divina Pastora'),(734,287,'Monseñor José Vicente de Unda'),(735,287,'Peña Blanca'),(736,288,'Capital Ospino'),(737,288,'Aparición'),(738,288,'La Estación'),(739,289,'Páez'),(740,289,'Payara'),(741,289,'Pimpinela'),(742,289,'Ramón Peraza'),(743,290,'Papelón'),(744,290,'Caño Delgadito'),(745,291,'San Genaro de Boconoito'),(746,291,'Antolín Tovar'),(747,292,'San Rafael de Onoto'),(748,292,'Santa Fe'),(749,292,'Thermo Morles'),(750,293,'Santa Rosalía'),(751,293,'Florida'),(752,294,'Sucre'),(753,294,'Concepción'),(754,294,'San Rafael de Palo Alzado'),(755,294,'Uvencio Antonio Velásquez'),(756,294,'San José de Saguaz'),(757,294,'Villa Rosa'),(758,295,'Turén'),(759,295,'Canelones'),(760,295,'Santa Cruz'),(761,295,'San Isidro Labrador'),(762,296,'Mariño'),(763,296,'Rómulo Gallegos'),(764,297,'San José de Aerocuar'),(765,297,'Tavera Acosta'),(766,298,'Río Caribe'),(767,298,'Antonio José de Sucre'),(768,298,'El Morro de Puerto Santo'),(769,298,'Puerto Santo'),(770,298,'San Juan de las Galdonas'),(771,299,'El Pilar'),(772,299,'El Rincón'),(773,299,'General Francisco Antonio Váquez'),(774,299,'Guaraúnos'),(775,299,'Tunapuicito'),(776,299,'Unión'),(777,300,'Santa Catalina'),(778,300,'Santa Rosa'),(779,300,'Santa Teresa'),(780,300,'Bolívar'),(781,300,'Maracapana'),(782,302,'Libertad'),(783,302,'El Paujil'),(784,302,'Yaguaraparo'),(785,303,'Cruz Salmerón Acosta'),(786,303,'Chacopata'),(787,303,'Manicuare'),(788,304,'Tunapuy'),(789,304,'Campo Elías'),(790,305,'Irapa'),(791,305,'Campo Claro'),(792,305,'Maraval'),(793,305,'San Antonio de Irapa'),(794,305,'Soro'),(795,306,'Mejía'),(796,307,'Cumanacoa'),(797,307,'Arenas'),(798,307,'Aricagua'),(799,307,'Cogollar'),(800,307,'San Fernando'),(801,307,'San Lorenzo'),(802,308,'Villa Frontado (Muelle de Cariaco)'),(803,308,'Catuaro'),(804,308,'Rendón'),(805,308,'San Cruz'),(806,308,'Santa María'),(807,309,'Altagracia'),(808,309,'Santa Inés'),(809,309,'Valentín Valiente'),(810,309,'Ayacucho'),(811,309,'San Juan'),(812,309,'Raúl Leoni'),(813,309,'Gran Mariscal'),(814,310,'Cristóbal Colón'),(815,310,'Bideau'),(816,310,'Punta de Piedras'),(817,310,'Güiria'),(818,341,'Andrés Bello'),(819,342,'Antonio Rómulo Costa'),(820,343,'Ayacucho'),(821,343,'Rivas Berti'),(822,343,'San Pedro del Río'),(823,344,'Bolívar'),(824,344,'Palotal'),(825,344,'General Juan Vicente Gómez'),(826,344,'Isaías Medina Angarita'),(827,345,'Cárdenas'),(828,345,'Amenodoro Ángel Lamus'),(829,345,'La Florida'),(830,346,'Córdoba'),(831,347,'Fernández Feo'),(832,347,'Alberto Adriani'),(833,347,'Santo Domingo'),(834,348,'Francisco de Miranda'),(835,349,'García de Hevia'),(836,349,'Boca de Grita'),(837,349,'José Antonio Páez'),(838,350,'Guásimos'),(839,351,'Independencia'),(840,351,'Juan Germán Roscio'),(841,351,'Román Cárdenas'),(842,352,'Jáuregui'),(843,352,'Emilio Constantino Guerrero'),(844,352,'Monseñor Miguel Antonio Salas'),(845,353,'José María Vargas'),(846,354,'Junín'),(847,354,'La Petrólea'),(848,354,'Quinimarí'),(849,354,'Bramón'),(850,355,'Libertad'),(851,355,'Cipriano Castro'),(852,355,'Manuel Felipe Rugeles'),(853,356,'Libertador'),(854,356,'Doradas'),(855,356,'Emeterio Ochoa'),(856,356,'San Joaquín de Navay'),(857,357,'Lobatera'),(858,357,'Constitución'),(859,358,'Michelena'),(860,359,'Panamericano'),(861,359,'La Palmita'),(862,360,'Pedro María Ureña'),(863,360,'Nueva Arcadia'),(864,361,'Delicias'),(865,361,'Pecaya'),(866,362,'Samuel Darío Maldonado'),(867,362,'Boconó'),(868,362,'Hernández'),(869,363,'La Concordia'),(870,363,'San Juan Bautista'),(871,363,'Pedro María Morantes'),(872,363,'San Sebastián'),(873,363,'Dr. Francisco Romero Lobo'),(874,364,'Seboruco'),(875,365,'Simón Rodríguez'),(876,366,'Sucre'),(877,366,'Eleazar López Contreras'),(878,366,'San Pablo'),(879,367,'Torbes'),(880,368,'Uribante'),(881,368,'Cárdenas'),(882,368,'Juan Pablo Peñalosa'),(883,368,'Potosí'),(884,369,'San Judas Tadeo'),(885,370,'Araguaney'),(886,370,'El Jaguito'),(887,370,'La Esperanza'),(888,370,'Santa Isabel'),(889,371,'Boconó'),(890,371,'El Carmen'),(891,371,'Mosquey'),(892,371,'Ayacucho'),(893,371,'Burbusay'),(894,371,'General Ribas'),(895,371,'Guaramacal'),(896,371,'Vega de Guaramacal'),(897,371,'Monseñor Jáuregui'),(898,371,'Rafael Rangel'),(899,371,'San Miguel'),(900,371,'San José'),(901,372,'Sabana Grande'),(902,372,'Cheregüé'),(903,372,'Granados'),(904,373,'Arnoldo Gabaldón'),(905,373,'Bolivia'),(906,373,'Carrillo'),(907,373,'Cegarra'),(908,373,'Chejendé'),(909,373,'Manuel Salvador Ulloa'),(910,373,'San José'),(911,374,'Carache'),(912,374,'La Concepción'),(913,374,'Cuicas'),(914,374,'Panamericana'),(915,374,'Santa Cruz'),(916,375,'Escuque'),(917,375,'La Unión'),(918,375,'Santa Rita'),(919,375,'Sabana Libre'),(920,376,'El Socorro'),(921,376,'Los Caprichos'),(922,376,'Antonio José de Sucre'),(923,377,'Campo Elías'),(924,377,'Arnoldo Gabaldón'),(925,378,'Santa Apolonia'),(926,378,'El Progreso'),(927,378,'La Ceiba'),(928,378,'Tres de Febrero'),(929,379,'El Dividive'),(930,379,'Agua Santa'),(931,379,'Agua Caliente'),(932,379,'El Cenizo'),(933,379,'Valerita'),(934,380,'Monte Carmelo'),(935,380,'Buena Vista'),(936,380,'Santa María del Horcón'),(937,381,'Motatán'),(938,381,'El Baño'),(939,381,'Jalisco'),(940,382,'Pampán'),(941,382,'Flor de Patria'),(942,382,'La Paz'),(943,382,'Santa Ana'),(944,383,'Pampanito'),(945,383,'La Concepción'),(946,383,'Pampanito II'),(947,384,'Betijoque'),(948,384,'José Gregorio Hernández'),(949,384,'La Pueblita'),(950,384,'Los Cedros'),(951,385,'Carvajal'),(952,385,'Campo Alegre'),(953,385,'Antonio Nicolás Briceño'),(954,385,'José Leonardo Suárez'),(955,386,'Sabana de Mendoza'),(956,386,'Junín'),(957,386,'Valmore Rodríguez'),(958,386,'El Paraíso'),(959,387,'Andrés Linares'),(960,387,'Chiquinquirá'),(961,387,'Cristóbal Mendoza'),(962,387,'Cruz Carrillo'),(963,387,'Matriz'),(964,387,'Monseñor Carrillo'),(965,387,'Tres Esquinas'),(966,388,'Cabimbú'),(967,388,'Jajó'),(968,388,'La Mesa de Esnujaque'),(969,388,'Santiago'),(970,388,'Tuñame'),(971,388,'La Quebrada'),(972,389,'Juan Ignacio Montilla'),(973,389,'La Beatriz'),(974,389,'La Puerta'),(975,389,'Mendoza del Valle de Momboy'),(976,389,'Mercedes Díaz'),(977,389,'San Luis'),(978,390,'Caraballeda'),(979,390,'Carayaca'),(980,390,'Carlos Soublette'),(981,390,'Caruao Chuspa'),(982,390,'Catia La Mar'),(983,390,'El Junko'),(984,390,'La Guaira'),(985,390,'Macuto'),(986,390,'Maiquetía'),(987,390,'Naiguatá'),(988,390,'Urimare'),(989,391,'Arístides Bastidas'),(990,392,'Bolívar'),(991,407,'Chivacoa'),(992,407,'Campo Elías'),(993,408,'Cocorote'),(994,409,'Independencia'),(995,410,'José Antonio Páez'),(996,411,'La Trinidad'),(997,412,'Manuel Monge'),(998,413,'Salóm'),(999,413,'Temerla'),(1000,413,'Nirgua'),(1001,414,'San Andrés'),(1002,414,'Yaritagua'),(1003,415,'San Javier'),(1004,415,'Albarico'),(1005,415,'San Felipe'),(1006,416,'Sucre'),(1007,417,'Urachiche'),(1008,418,'El Guayabo'),(1009,418,'Farriar'),(1010,441,'Isla de Toas'),(1011,441,'Monagas'),(1012,442,'San Timoteo'),(1013,442,'General Urdaneta'),(1014,442,'Libertador'),(1015,442,'Marcelino Briceño'),(1016,442,'Pueblo Nuevo'),(1017,442,'Manuel Guanipa Matos'),(1018,443,'Ambrosio'),(1019,443,'Carmen Herrera'),(1020,443,'La Rosa'),(1021,443,'Germán Ríos Linares'),(1022,443,'San Benito'),(1023,443,'Rómulo Betancourt'),(1024,443,'Jorge Hernández'),(1025,443,'Punta Gorda'),(1026,443,'Arístides Calvani'),(1027,444,'Encontrados'),(1028,444,'Udón Pérez'),(1029,445,'Moralito'),(1030,445,'San Carlos del Zulia'),(1031,445,'Santa Cruz del Zulia'),(1032,445,'Santa Bárbara'),(1033,445,'Urribarrí'),(1034,446,'Carlos Quevedo'),(1035,446,'Francisco Javier Pulgar'),(1036,446,'Simón Rodríguez'),(1037,446,'Guamo-Gavilanes'),(1038,448,'La Concepción'),(1039,448,'San José'),(1040,448,'Mariano Parra León'),(1041,448,'José Ramón Yépez'),(1042,449,'Jesús María Semprún'),(1043,449,'Barí'),(1044,450,'Concepción'),(1045,450,'Andrés Bello'),(1046,450,'Chiquinquirá'),(1047,450,'El Carmelo'),(1048,450,'Potreritos'),(1049,451,'Libertad'),(1050,451,'Alonso de Ojeda'),(1051,451,'Venezuela'),(1052,451,'Eleazar López Contreras'),(1053,451,'Campo Lara'),(1054,452,'Bartolomé de las Casas'),(1055,452,'Libertad'),(1056,452,'Río Negro'),(1057,452,'San José de Perijá'),(1058,453,'San Rafael'),(1059,453,'La Sierrita'),(1060,453,'Las Parcelas'),(1061,453,'Luis de Vicente'),(1062,453,'Monseñor Marcos Sergio Godoy'),(1063,453,'Ricaurte'),(1064,453,'Tamare'),(1065,454,'Antonio Borjas Romero'),(1066,454,'Bolívar'),(1067,454,'Cacique Mara'),(1068,454,'Carracciolo Parra Pérez'),(1069,454,'Cecilio Acosta'),(1070,454,'Cristo de Aranza'),(1071,454,'Coquivacoa'),(1072,454,'Chiquinquirá'),(1073,454,'Francisco Eugenio Bustamante'),(1074,454,'Idelfonzo Vásquez'),(1075,454,'Juana de Ávila'),(1076,454,'Luis Hurtado Higuera'),(1077,454,'Manuel Dagnino'),(1078,454,'Olegario Villalobos'),(1079,454,'Raúl Leoni'),(1080,454,'Santa Lucía'),(1081,454,'Venancio Pulgar'),(1082,454,'San Isidro'),(1083,455,'Altagracia'),(1084,455,'Faría'),(1085,455,'Ana María Campos'),(1086,455,'San Antonio'),(1087,455,'San José'),(1088,456,'Donaldo García'),(1089,456,'El Rosario'),(1090,456,'Sixto Zambrano'),(1091,457,'San Francisco'),(1092,457,'El Bajo'),(1093,457,'Domitila Flores'),(1094,457,'Francisco Ochoa'),(1095,457,'Los Cortijos'),(1096,457,'Marcial Hernández'),(1097,458,'Santa Rita'),(1098,458,'El Mene'),(1099,458,'Pedro Lucas Urribarrí'),(1100,458,'José Cenobio Urribarrí'),(1101,459,'Rafael Maria Baralt'),(1102,459,'Manuel Manrique'),(1103,459,'Rafael Urdaneta'),(1104,460,'Bobures'),(1105,460,'Gibraltar'),(1106,460,'Heras'),(1107,460,'Monseñor Arturo Álvarez'),(1108,460,'Rómulo Gallegos'),(1109,460,'El Batey'),(1110,461,'Rafael Urdaneta'),(1111,461,'La Victoria'),(1112,461,'Raúl Cuenca'),(1113,447,'Sinamaica'),(1114,447,'Alta Guajira'),(1115,447,'Elías Sánchez Rubio'),(1116,447,'Guajira'),(1117,462,'Altagracia'),(1118,462,'Antímano'),(1119,462,'Caricuao'),(1120,462,'Catedral'),(1121,462,'Coche'),(1122,462,'El Junquito'),(1123,462,'El Paraíso'),(1124,462,'El Recreo'),(1125,462,'El Valle'),(1126,462,'La Candelaria'),(1127,462,'La Pastora'),(1128,462,'La Vega'),(1129,462,'Macarao'),(1130,462,'San Agustín'),(1131,462,'San Bernardino'),(1132,462,'San José'),(1133,462,'San Juan'),(1134,462,'San Pedro'),(1135,462,'Santa Rosalía'),(1136,462,'Santa Teresa'),(1137,462,'Sucre (Catia)'),(1138,462,'23 de enero');
/*!40000 ALTER TABLE `parroquias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permiso`
--

DROP TABLE IF EXISTS `permiso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permiso` (
  `id_permisos` int(11) NOT NULL AUTO_INCREMENT,
  `permiso` varchar(50) NOT NULL,
  PRIMARY KEY (`id_permisos`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permiso`
--

LOCK TABLES `permiso` WRITE;
/*!40000 ALTER TABLE `permiso` DISABLE KEYS */;
INSERT INTO `permiso` VALUES (1,'Vacaciones'),(2,'Proceso Administrativo');
/*!40000 ALTER TABLE `permiso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preguntas`
--

DROP TABLE IF EXISTS `preguntas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preguntas` (
  `id_preguntas` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL,
  `pregunta1` varchar(100) NOT NULL,
  `pregunta2` varchar(100) NOT NULL,
  `pregunta3` varchar(100) NOT NULL,
  PRIMARY KEY (`id_preguntas`),
  KEY `idUser` (`idUser`),
  CONSTRAINT `preguntas_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preguntas`
--

LOCK TABLES `preguntas` WRITE;
/*!40000 ALTER TABLE `preguntas` DISABLE KEYS */;
/*!40000 ALTER TABLE `preguntas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prescripcion`
--

DROP TABLE IF EXISTS `prescripcion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prescripcion` (
  `id_pre` int(11) NOT NULL AUTO_INCREMENT,
  `idAtencion` int(11) NOT NULL,
  `idMedicamentos` int(11) NOT NULL,
  `dosis` varchar(50) NOT NULL,
  `frecuencia` varchar(50) NOT NULL,
  `duracion` varchar(50) NOT NULL,
  `fecha` varchar(10) NOT NULL,
  `hora` varchar(20) NOT NULL,
  PRIMARY KEY (`id_pre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescripcion`
--

LOCK TABLES `prescripcion` WRITE;
/*!40000 ALTER TABLE `prescripcion` DISABLE KEYS */;
/*!40000 ALTER TABLE `prescripcion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `respuestas`
--

DROP TABLE IF EXISTS `respuestas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `respuestas` (
  `idPreguntas` int(11) NOT NULL,
  `respuesta1` varchar(100) NOT NULL,
  `respuesta2` varchar(100) NOT NULL,
  `respuesta3` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `respuestas`
--

LOCK TABLES `respuestas` WRITE;
/*!40000 ALTER TABLE `respuestas` DISABLE KEYS */;
/*!40000 ALTER TABLE `respuestas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol`
--

DROP TABLE IF EXISTS `rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `rol` varchar(50) NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol`
--

LOCK TABLES `rol` WRITE;
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
INSERT INTO `rol` VALUES (1,'Administrador'),(2,'Medico'),(3,'Informatica');
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitudes`
--

DROP TABLE IF EXISTS `solicitudes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `solicitudes` (
  `id_solic` int(11) DEFAULT NULL,
  `idTiposolic` int(11) NOT NULL,
  `solicitud` int(11) NOT NULL,
  `fechaSolic` varchar(15) NOT NULL,
  `horaSolic` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitudes`
--

LOCK TABLES `solicitudes` WRITE;
/*!40000 ALTER TABLE `solicitudes` DISABLE KEYS */;
/*!40000 ALTER TABLE `solicitudes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sueldo`
--

DROP TABLE IF EXISTS `sueldo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sueldo` (
  `id_sueldo` int(11) DEFAULT NULL,
  `idPersonal` int(11) NOT NULL,
  `idGrado` int(11) NOT NULL,
  `idBeneficio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sueldo`
--

LOCK TABLES `sueldo` WRITE;
/*!40000 ALTER TABLE `sueldo` DISABLE KEYS */;
/*!40000 ALTER TABLE `sueldo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tiposolicitud`
--

DROP TABLE IF EXISTS `tiposolicitud`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tiposolicitud` (
  `id_tiposolic` int(11) DEFAULT NULL,
  `solicitus` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tiposolicitud`
--

LOCK TABLES `tiposolicitud` WRITE;
/*!40000 ALTER TABLE `tiposolicitud` DISABLE KEYS */;
/*!40000 ALTER TABLE `tiposolicitud` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `translados`
--

DROP TABLE IF EXISTS `translados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `translados` (
  `id_translados` int(11) DEFAULT NULL,
  `idPersonal` int(11) NOT NULL,
  `idDependencia` int(11) NOT NULL,
  `idEstatus` int(11) NOT NULL,
  `idCargo` int(11) NOT NULL,
  `fecha` varchar(10) NOT NULL,
  `hora` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `translados`
--

LOCK TABLES `translados` WRITE;
/*!40000 ALTER TABLE `translados` DISABLE KEYS */;
/*!40000 ALTER TABLE `translados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ubicacion`
--

DROP TABLE IF EXISTS `ubicacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ubicacion` (
  `id_empleadoUbi` int(11) NOT NULL,
  `idEstado` int(11) NOT NULL,
  `idCiudad` int(11) NOT NULL,
  `idMunicipio` int(11) NOT NULL,
  `idParroquia` int(11) NOT NULL,
  `calle` text DEFAULT NULL,
  `vivienda` varchar(50) NOT NULL,
  `nombreVivienda` varchar(60) DEFAULT NULL,
  `numVivienda` varchar(50) DEFAULT NULL,
  `pisoVivienda` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ubicacion`
--

LOCK TABLES `ubicacion` WRITE;
/*!40000 ALTER TABLE `ubicacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `ubicacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `idEmpleado` int(11) DEFAULT NULL,
  `idRol` int(11) DEFAULT NULL,
  `nameUser` varchar(100) NOT NULL,
  `userPassword` varchar(150) NOT NULL,
  `permiso` int(3) DEFAULT NULL,
  `prioridad` int(2) DEFAULT NULL,
  `pin` int(5) DEFAULT NULL,
  `activo` int(1) DEFAULT NULL,
  `enUso` int(1) NOT NULL,
  `fotoPérfil` varchar(150) DEFAULT NULL,
  `fecha` varchar(10) DEFAULT NULL,
  `hora` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  KEY `idPersonal` (`idEmpleado`),
  KEY `idRol` (`idRol`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`idRol`) REFERENCES `rol` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`idEmpleado`) REFERENCES `datosempleados` (`id_empleados`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,1,'Jeison12345','ff2c1c5042200d8a7e6802fe3447281a6979e5b49a9c3ca3e9f24c6303486cf493ad4ffb6adac23930e60b17d5a13d19',NULL,NULL,NULL,1,1,NULL,NULL,NULL),(11,73,2,'Emili123','d43d21546739cf0e6ba7f20e703123cd282eaf4a41fc313d851719cee4ab7700790efe72aabc8d520e5e23861319e925',NULL,NULL,NULL,2,1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-02-27 14:14:48
