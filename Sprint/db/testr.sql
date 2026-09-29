-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-09-2026 a las 19:09:19
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `testr`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `categoriaID` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `desc` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `comentarioID` int(11) NOT NULL,
  `publicacionID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `fechacreacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historialbusqueda`
--

CREATE TABLE `historialbusqueda` (
  `busquedaID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `busqueda` varchar(255) NOT NULL,
  `fechahora` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historialreproduccion`
--

CREATE TABLE `historialreproduccion` (
  `reproduccionID` int(11) NOT NULL,
  `publicacionID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `fechahora` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `like`
--

CREATE TABLE `like` (
  `likeID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `publicacionID` int(11) DEFAULT NULL,
  `comentarioID` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicaciones`
--

CREATE TABLE `publicaciones` (
  `publicacionID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `categoriaID` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `contenido` text NOT NULL,
  `fechacreacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `reporteID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `publicacionID` int(11) DEFAULT NULL,
  `comentarioID` int(11) DEFAULT NULL,
  `motivo` varchar(100) NOT NULL,
  `desc` text DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'Pendiente',
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repost`
--

CREATE TABLE `repost` (
  `repostID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `publicacionID` int(11) NOT NULL,
  `fecharepost` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `usuarioID` int(11) NOT NULL,
  `nombreusuario` varchar(50) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `desc` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `registro` datetime DEFAULT current_timestamp(),
  `estado` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`categoriaID`);

--
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`comentarioID`),
  ADD KEY `publicacionID` (`publicacionID`),
  ADD KEY `usuarioID` (`usuarioID`);

--
-- Indices de la tabla `historialbusqueda`
--
ALTER TABLE `historialbusqueda`
  ADD PRIMARY KEY (`busquedaID`),
  ADD KEY `usuarioID` (`usuarioID`);

--
-- Indices de la tabla `historialreproduccion`
--
ALTER TABLE `historialreproduccion`
  ADD PRIMARY KEY (`reproduccionID`),
  ADD KEY `publicacionID` (`publicacionID`),
  ADD KEY `usuarioID` (`usuarioID`);

--
-- Indices de la tabla `like`
--
ALTER TABLE `like`
  ADD PRIMARY KEY (`likeID`),
  ADD KEY `usuarioID` (`usuarioID`),
  ADD KEY `publicacionID` (`publicacionID`),
  ADD KEY `comentarioID` (`comentarioID`);

--
-- Indices de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`publicacionID`),
  ADD KEY `usuarioID` (`usuarioID`),
  ADD KEY `categoriaID` (`categoriaID`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`reporteID`),
  ADD KEY `usuarioID` (`usuarioID`),
  ADD KEY `publicacionID` (`publicacionID`),
  ADD KEY `comentarioID` (`comentarioID`);

--
-- Indices de la tabla `repost`
--
ALTER TABLE `repost`
  ADD PRIMARY KEY (`repostID`),
  ADD KEY `usuarioID` (`usuarioID`),
  ADD KEY `publicacionID` (`publicacionID`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`usuarioID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `categoriaID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `comentarioID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historialbusqueda`
--
ALTER TABLE `historialbusqueda`
  MODIFY `busquedaID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historialreproduccion`
--
ALTER TABLE `historialreproduccion`
  MODIFY `reproduccionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `like`
--
ALTER TABLE `like`
  MODIFY `likeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `publicacionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `reporteID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `repost`
--
ALTER TABLE `repost`
  MODIFY `repostID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `usuarioID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`publicacionID`) REFERENCES `publicaciones` (`publicacionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `comentarios_ibfk_2` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `historialbusqueda`
--
ALTER TABLE `historialbusqueda`
  ADD CONSTRAINT `historialbusqueda_ibfk_1` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `historialreproduccion`
--
ALTER TABLE `historialreproduccion`
  ADD CONSTRAINT `historialreproduccion_ibfk_1` FOREIGN KEY (`publicacionID`) REFERENCES `publicaciones` (`publicacionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `historialreproduccion_ibfk_2` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `like`
--
ALTER TABLE `like`
  ADD CONSTRAINT `like_ibfk_1` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`) ON DELETE CASCADE,
  ADD CONSTRAINT `like_ibfk_2` FOREIGN KEY (`publicacionID`) REFERENCES `publicaciones` (`publicacionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `like_ibfk_3` FOREIGN KEY (`comentarioID`) REFERENCES `comentarios` (`comentarioID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD CONSTRAINT `publicaciones_ibfk_1` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`) ON DELETE CASCADE,
  ADD CONSTRAINT `publicaciones_ibfk_2` FOREIGN KEY (`categoriaID`) REFERENCES `categoria` (`categoriaID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `reportes_ibfk_1` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reportes_ibfk_2` FOREIGN KEY (`publicacionID`) REFERENCES `publicaciones` (`publicacionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reportes_ibfk_3` FOREIGN KEY (`comentarioID`) REFERENCES `comentarios` (`comentarioID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `repost`
--
ALTER TABLE `repost`
  ADD CONSTRAINT `repost_ibfk_1` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`) ON DELETE CASCADE,
  ADD CONSTRAINT `repost_ibfk_2` FOREIGN KEY (`publicacionID`) REFERENCES `publicaciones` (`publicacionID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
