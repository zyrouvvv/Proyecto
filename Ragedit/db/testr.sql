

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";






CREATE TABLE `categoria` (
  `categoriaID` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `desc` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `comentarios` (
  `comentarioID` int(11) NOT NULL,
  `publicacionID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `fechacreacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `historialbusqueda` (
  `busquedaID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `busqueda` varchar(255) NOT NULL,
  `fechahora` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `historialreproduccion` (
  `reproduccionID` int(11) NOT NULL,
  `publicacionID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `fechahora` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `like` (
  `likeID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `publicacionID` int(11) DEFAULT NULL,
  `comentarioID` int(11) DEFAULT NULL,
  `tipo` varchar(20) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `publicaciones` (
  `publicacionID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `categoriaID` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `contenido` text NOT NULL,
  `fechacreacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



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



CREATE TABLE `repost` (
  `repostID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `publicacionID` int(11) NOT NULL,
  `fecharepost` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `usuarios` (
  `usuarioID` int(11) NOT NULL,
  `nombreusuario` varchar(50) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `desc` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `registro` datetime DEFAULT current_timestamp(),
  `estado` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `rol` varchar(20) NOT NULL DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `categoria`
  ADD PRIMARY KEY (`categoriaID`);


ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`comentarioID`),
  ADD KEY `publicacionID` (`publicacionID`),
  ADD KEY `usuarioID` (`usuarioID`);


ALTER TABLE `historialbusqueda`
  ADD PRIMARY KEY (`busquedaID`),
  ADD KEY `usuarioID` (`usuarioID`);


ALTER TABLE `historialreproduccion`
  ADD PRIMARY KEY (`reproduccionID`),
  ADD KEY `publicacionID` (`publicacionID`),
  ADD KEY `usuarioID` (`usuarioID`);


ALTER TABLE `like`
  ADD PRIMARY KEY (`likeID`),
  ADD KEY `usuarioID` (`usuarioID`),
  ADD KEY `publicacionID` (`publicacionID`),
  ADD KEY `comentarioID` (`comentarioID`),
  ADD UNIQUE KEY `usuario_publicacion_unica` (`usuarioID`,`publicacionID`);


ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`publicacionID`),
  ADD KEY `usuarioID` (`usuarioID`),
  ADD KEY `categoriaID` (`categoriaID`);


ALTER TABLE `reportes`
  ADD PRIMARY KEY (`reporteID`),
  ADD KEY `usuarioID` (`usuarioID`),
  ADD KEY `publicacionID` (`publicacionID`),
  ADD KEY `comentarioID` (`comentarioID`);

ALTER TABLE `repost`
  ADD PRIMARY KEY (`repostID`),
  ADD KEY `usuarioID` (`usuarioID`),
  ADD KEY `publicacionID` (`publicacionID`);


ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`usuarioID`),
  ADD UNIQUE KEY `email` (`email`);



ALTER TABLE `categoria`
  MODIFY `categoriaID` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `comentarios`
  MODIFY `comentarioID` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `historialbusqueda`
  MODIFY `busquedaID` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `historialreproduccion`
  MODIFY `reproduccionID` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `like`
  MODIFY `likeID` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `publicaciones`
  MODIFY `publicacionID` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `reportes`
  MODIFY `reporteID` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `repost`
  MODIFY `repostID` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `usuarios`
  MODIFY `usuarioID` int(11) NOT NULL AUTO_INCREMENT;




ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`publicacionID`) REFERENCES `publicaciones` (`publicacionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `comentarios_ibfk_2` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`) ON DELETE CASCADE;

ALTER TABLE `historialbusqueda`
  ADD CONSTRAINT `historialbusqueda_ibfk_1` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`) ON DELETE CASCADE;


ALTER TABLE `historialreproduccion`
  ADD CONSTRAINT `historialreproduccion_ibfk_1` FOREIGN KEY (`publicacionID`) REFERENCES `publicaciones` (`publicacionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `historialreproduccion_ibfk_2` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`) ON DELETE CASCADE;


ALTER TABLE `like`
  ADD CONSTRAINT `like_ibfk_1` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`) ON DELETE CASCADE,
  ADD CONSTRAINT `like_ibfk_2` FOREIGN KEY (`publicacionID`) REFERENCES `publicaciones` (`publicacionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `like_ibfk_3` FOREIGN KEY (`comentarioID`) REFERENCES `comentarios` (`comentarioID`) ON DELETE CASCADE;

ALTER TABLE `publicaciones`
  ADD CONSTRAINT `publicaciones_ibfk_1` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`) ON DELETE CASCADE,
  ADD CONSTRAINT `publicaciones_ibfk_2` FOREIGN KEY (`categoriaID`) REFERENCES `categoria` (`categoriaID`) ON DELETE CASCADE;


ALTER TABLE `reportes`
  ADD CONSTRAINT `reportes_ibfk_1` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reportes_ibfk_2` FOREIGN KEY (`publicacionID`) REFERENCES `publicaciones` (`publicacionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reportes_ibfk_3` FOREIGN KEY (`comentarioID`) REFERENCES `comentarios` (`comentarioID`) ON DELETE CASCADE;


ALTER TABLE `repost`
  ADD CONSTRAINT `repost_ibfk_1` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`) ON DELETE CASCADE,
  ADD CONSTRAINT `repost_ibfk_2` FOREIGN KEY (`publicacionID`) REFERENCES `publicaciones` (`publicacionID`) ON DELETE CASCADE;
COMMIT;

