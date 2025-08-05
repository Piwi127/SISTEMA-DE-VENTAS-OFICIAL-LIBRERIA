-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-08-2024 a las 00:00:00
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
-- Base de datos: `sistema_ventas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `nombre_empresa` varchar(255) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `nit` varchar(50) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `cuerpo_mensaje` text NOT NULL,
  `fecha_recordatorio` datetime NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `stock_minimo` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','empleado') NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `tipo_documento` enum('boleta','factura') NOT NULL,
  `numero_documento` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `direccion`, `telefono`, `email`, `fecha_registro`) VALUES
(1, 'Cliente General', 'Dirección General', '123456789', 'cliente@general.com', '2023-10-26 00:00:00');

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nombre_empresa`, `direccion`, `telefono`, `email`, `nit`, `logo_url`, `fecha_actualizacion`) VALUES
(1, 'Librería Belén', 'Av. Principal 123', '987654321', 'info@libreriabelen.com', '123456789-0', NULL, '2023-10-26 00:00:00');

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `password`, `rol`, `fecha_registro`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2023-10-26 00:00:00');

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `codigo`, `nombre`, `descripcion`, `categoria_id`, `precio`, `stock`, `stock_minimo`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'PROD001', 'Libro de Matemáticas', 'Libro de texto para nivel secundario', 1, 25.00, 100, 10, 1, '2023-10-26 00:00:00', '2023-10-26 00:00:00'),
(2, 'PROD002', 'Cuaderno Espiral', 'Cuaderno A4 de 100 hojas', 2, 4.00, 200, 20, 1, '2023-10-26 00:00:00', '2023-10-26 00:00:00'),
(3, 'PROD003', 'Bolígrafo Azul', 'Bolígrafo de tinta azul, punta fina', 3, 1.50, 500, 50, 1, '2023-10-26 00:00:00', '2023-10-26 00:00:00');

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `cliente_id`, `user_id`, `fecha_venta`, `total`, `tipo_documento`, `numero_documento`) VALUES
(1, 1, 1, '2023-10-26 10:00:00', 29.00, 'boleta', 'B001-0000001');

--
-- Volcado de datos para la tabla `detalle_venta`
--

INSERT INTO `detalle_venta` (`id`, `venta_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 1, 1, 25.00, 25.00),
(2, 1, 3, 2, 1.50, 3.00);

--
-- Volcado de datos para la tabla `notas`
--

INSERT INTO `notas` (`id`, `user_id`, `asunto`, `cuerpo_mensaje`, `fecha_recordatorio`, `fecha_creacion`) VALUES
(1, 1, 'Reunión de equipo', 'Recordar reunión semanal de equipo el viernes a las 10 AM', '2023-11-03 10:00:00', '2023-10-30 15:30:00');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Filtros para tablas volcadas
--

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `detalle_venta_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_venta_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `notas`
--
ALTER TABLE `notas`
  ADD CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;






--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nombre_empresa`, `direccion`, `telefono`, `email`, `nit`, `logo_url`) VALUES
(1, 'Librería Belén', 'Calle Falsa 123', '123456789', 'info@libreriabelen.com', '123456-7', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `stock_minimo` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `estado` enum('activa','anulada') NOT NULL DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `cuerpo_mensaje` text NOT NULL,
  `fecha_recordatorio` datetime NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','empleado') NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `password`, `rol`, `fecha_registro`) VALUES
(1, 'admin', '$2y$10$Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.2.Q.