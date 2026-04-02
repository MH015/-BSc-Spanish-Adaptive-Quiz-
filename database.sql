-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 02, 2026 at 03:35 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `adaptive_quiz`
--

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` int NOT NULL,
  `user_id` int NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question_id` int NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_answer` char(1) NOT NULL,
  `difficulty` enum('easy','medium','hard') NOT NULL,
  `category` varchar(50) NOT NULL
) ;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `difficulty`, `category`) VALUES
(1, 'What is the Spanish word for \"water\"?', 'Agua', 'Leche', 'Jugo', 'Café', 'a', 'easy', 'Vocabulary'),
(2, 'How do you say \"house\" in Spanish?', 'Carro', 'Casa', 'Cama', 'Cosa', 'b', 'easy', 'Vocabulary'),
(3, 'What does \"perro\" mean in English?', 'Cat', 'Bird', 'Dog', 'Fish', 'c', 'easy', 'Vocabulary'),
(4, 'What is the Spanish word for \"book\"?', 'Mesa', 'Silla', 'Libro', 'Papel', 'c', 'easy', 'Vocabulary'),
(5, 'How do you say \"red\" in Spanish?', 'Azul', 'Verde', 'Amarillo', 'Rojo', 'd', 'easy', 'Vocabulary'),
(6, 'What does \"gato\" mean in English?', 'Dog', 'Cat', 'Mouse', 'Rabbit', 'b', 'easy', 'Vocabulary'),
(7, 'What is the Spanish word for \"food\"?', 'Comida', 'Bebida', 'Ropa', 'Dinero', 'a', 'easy', 'Vocabulary'),
(8, 'How do you say \"friend\" in Spanish?', 'Hermano', 'Primo', 'Amigo', 'Vecino', 'c', 'easy', 'Vocabulary'),
(9, 'What does \"mariposa\" mean in English?', 'Ladybug', 'Butterfly', 'Dragonfly', 'Bee', 'b', 'medium', 'Vocabulary'),
(10, 'What is the Spanish word for \"keyboard\"?', 'Ratón', 'Pantalla', 'Teclado', 'Ordenador', 'c', 'medium', 'Vocabulary'),
(11, 'How do you say \"newspaper\" in Spanish?', 'Revista', 'Periódico', 'Libro', 'Carta', 'b', 'medium', 'Vocabulary'),
(12, 'What does \"estrella\" mean in English?', 'Moon', 'Sun', 'Star', 'Cloud', 'c', 'medium', 'Vocabulary'),
(13, 'What is the Spanish word for \"shoulder\"?', 'Codo', 'Hombro', 'Rodilla', 'Tobillo', 'b', 'medium', 'Vocabulary'),
(14, 'How do you say \"to succeed\" in Spanish?', 'Fallar', 'Intentar', 'Lograr', 'Perder', 'c', 'medium', 'Vocabulary'),
(15, 'What does \"paisaje\" mean in English?', 'Country', 'Landscape', 'City', 'Village', 'b', 'medium', 'Vocabulary'),
(16, 'What is the Spanish word for \"rainbow\"?', 'Arcoíris', 'Tormenta', 'Nube', 'Relámpago', 'a', 'medium', 'Vocabulary'),
(17, 'What does \"resbaloso\" mean in English?', 'Rough', 'Slippery', 'Smooth', 'Hard', 'b', 'hard', 'Vocabulary'),
(18, 'What is the Spanish word for \"drought\"?', 'Inundación', 'Sequía', 'Tormenta', 'Terremoto', 'b', 'hard', 'Vocabulary'),
(19, 'How do you say \"to overthrow\" in Spanish?', 'Apoyar', 'Derrocar', 'Elegir', 'Gobernar', 'b', 'hard', 'Vocabulary'),
(20, 'What does \"angustia\" mean in English?', 'Happiness', 'Anger', 'Anguish', 'Confusion', 'c', 'hard', 'Vocabulary'),
(21, 'What is the Spanish word for \"withdrawal\"?', 'Depósito', 'Retiro', 'Préstamo', 'Inversión', 'b', 'hard', 'Vocabulary'),
(22, 'How do you say \"nevertheless\" in Spanish?', 'Además', 'Sin embargo', 'Por eso', 'Aunque', 'b', 'hard', 'Vocabulary'),
(23, 'What does \"escalofrío\" mean in English?', 'Fever', 'Shiver/Chill', 'Sweat', 'Headache', 'b', 'hard', 'Vocabulary'),
(24, 'What is the Spanish word for \"bewilderment\"?', 'Claridad', 'Desconcierto', 'Certeza', 'Confianza', 'b', 'hard', 'Vocabulary'),
(25, 'Which article goes with \"casa\" (house)?', 'El', 'La', 'Los', 'Las', 'b', 'easy', 'Grammar'),
(26, 'What is the plural of \"libro\" (book)?', 'Libros', 'Libroes', 'Libras', 'Libro', 'a', 'easy', 'Grammar'),
(27, 'Which is the correct way to say \"I am happy\"?', 'Yo soy feliz', 'Yo es feliz', 'Yo somos feliz', 'Yo son feliz', 'a', 'easy', 'Grammar'),
(28, '\"El\" is used for which type of nouns?', 'Feminine singular', 'Masculine singular', 'Feminine plural', 'Masculine plural', 'b', 'easy', 'Grammar'),
(29, 'What is the feminine form of \"alto\" (tall)?', 'Alto', 'Alta', 'Altos', 'Altas', 'b', 'easy', 'Grammar'),
(30, 'Which pronoun means \"we\" in Spanish?', 'Yo', 'Tú', 'Nosotros', 'Ellos', 'c', 'easy', 'Grammar'),
(31, 'How do you make \"grande\" plural?', 'Grandos', 'Grandes', 'Grandas', 'Grandies', 'b', 'easy', 'Grammar'),
(32, 'Which word means \"the\" (feminine plural)?', 'El', 'La', 'Los', 'Las', 'd', 'easy', 'Grammar'),
(33, 'Which sentence uses \"ser\" correctly?', 'Ella es cansada', 'Ella es de México', 'Ella es en casa', 'Ella es comiendo', 'b', 'medium', 'Grammar'),
(34, 'When do you use \"estar\" instead of \"ser\"?', 'For nationality', 'For temporary states', 'For occupation', 'For physical description', 'b', 'medium', 'Grammar'),
(35, 'What is the correct possessive for \"their\" (masculine)?', 'Su', 'Sus', 'Suyo', 'Suyos', 'a', 'medium', 'Grammar'),
(36, 'Which is the correct comparative: \"more tall\"?', 'Más alto', 'Más grande', 'Mucho alto', 'Muy alto', 'a', 'medium', 'Grammar'),
(37, '\"Gustar\" literally means what?', 'To like', 'To please', 'To want', 'To love', 'b', 'medium', 'Grammar'),
(38, 'What is the correct indirect object pronoun for \"to him\"?', 'Lo', 'Le', 'La', 'Les', 'b', 'medium', 'Grammar'),
(39, 'Which preposition means \"between\"?', 'Sobre', 'Bajo', 'Entre', 'Hacia', 'c', 'medium', 'Grammar'),
(40, 'How do you form the superlative of \"bueno\"?', 'Más bueno', 'El mejor', 'El más bueno', 'Buenísimo', 'b', 'medium', 'Grammar'),
(41, 'Which sentence correctly uses the subjunctive?', 'Quiero que vienes', 'Quiero que vengas', 'Quiero que venir', 'Quiero que vendrás', 'b', 'hard', 'Grammar'),
(42, 'When is the subjunctive mood typically used?', 'For facts', 'For doubts and wishes', 'For past actions', 'For daily routines', 'b', 'hard', 'Grammar'),
(43, 'What is the past subjunctive of \"hablar\" (yo)?', 'Hablé', 'Hablaba', 'Hablara', 'Hablaré', 'c', 'hard', 'Grammar'),
(44, 'Which sentence uses \"por\" correctly?', 'Voy por la tienda', 'Gracias por tu ayuda', 'Salgo por las ocho', 'Estudio por ser médico', 'b', 'hard', 'Grammar'),
(45, '\"Si tuviera dinero, viajaría\" is an example of:', 'Present tense', 'Conditional sentence', 'Imperative mood', 'Future tense', 'b', 'hard', 'Grammar'),
(46, 'What triggers the subjunctive in \"Dudo que...\"?', 'Certainty', 'Doubt/Uncertainty', 'Past action', 'Future plan', 'b', 'hard', 'Grammar'),
(47, 'Which is the correct passive voice: \"The book was written\"?', 'El libro escribió', 'El libro fue escrito', 'El libro era escribir', 'El libro siendo escrito', 'b', 'hard', 'Grammar'),
(48, 'What is the difference between \"qué\" and \"cuál\"?', 'There is no difference', 'Qué for definitions, cuál for choices', 'Qué for questions, cuál for statements', 'Cuál for definitions, qué for choices', 'b', 'hard', 'Grammar'),
(49, 'What is \"hablar\" (to speak) conjugated for \"yo\"?', 'Hablo', 'Hablas', 'Habla', 'Hablan', 'a', 'easy', 'Verbs'),
(50, 'How do you say \"he eats\" in Spanish?', 'Yo como', 'Él come', 'Ella comer', 'Ellos comen', 'b', 'easy', 'Verbs'),
(51, 'What does \"vivir\" mean?', 'To eat', 'To speak', 'To live', 'To run', 'c', 'easy', 'Verbs'),
(52, 'Conjugate \"escribir\" for \"nosotros\":', 'Escribo', 'Escribes', 'Escribimos', 'Escriben', 'c', 'easy', 'Verbs'),
(53, 'What is the infinitive ending for \"-ar\" verbs?', '-er', '-ir', '-ar', '-or', 'c', 'easy', 'Verbs'),
(54, 'How do you say \"they speak\" in Spanish?', 'Habla', 'Hablamos', 'Hablan', 'Habláis', 'c', 'easy', 'Verbs'),
(55, 'What does \"beber\" mean?', 'To eat', 'To drink', 'To run', 'To walk', 'b', 'easy', 'Verbs'),
(56, 'Conjugate \"caminar\" (to walk) for \"tú\":', 'Camino', 'Caminas', 'Camina', 'Caminan', 'b', 'easy', 'Verbs'),
(57, 'What is the \"yo\" form of \"tener\" (to have)?', 'Teno', 'Tiene', 'Tengo', 'Tieno', 'c', 'medium', 'Verbs'),
(58, 'Conjugate \"ir\" (to go) for \"nosotros\":', 'Vamos', 'Vemos', 'Vimos', 'Vayamos', 'a', 'medium', 'Verbs'),
(59, 'What is the preterite (past) of \"hablar\" for \"yo\"?', 'Hablé', 'Hablaba', 'Hablaré', 'Hablo', 'a', 'medium', 'Verbs'),
(60, 'How do you say \"I went\" in Spanish?', 'Yo iba', 'Yo fui', 'Yo iré', 'Yo voy', 'b', 'medium', 'Verbs'),
(61, 'What is the \"él/ella\" form of \"ser\" (to be)?', 'Soy', 'Eres', 'Es', 'Somos', 'c', 'medium', 'Verbs'),
(62, 'Conjugate \"hacer\" (to do/make) for \"tú\":', 'Hago', 'Haces', 'Hace', 'Hacen', 'b', 'medium', 'Verbs'),
(63, 'What is the imperfect tense of \"comer\" for \"yo\"?', 'Comí', 'Comía', 'Comeré', 'Como', 'b', 'medium', 'Verbs'),
(64, 'How do you say \"she was\" (permanent) in Spanish?', 'Ella estaba', 'Ella era', 'Ella fue', 'Ella estuvo', 'b', 'medium', 'Verbs'),
(65, 'What is the future tense of \"saber\" for \"yo\"?', 'Sabré', 'Saberé', 'Saperé', 'Sabiré', 'a', 'hard', 'Verbs'),
(66, 'Conjugate \"decir\" (to say) in preterite for \"él\":', 'Decí', 'Dijo', 'Dicío', 'Decía', 'b', 'hard', 'Verbs'),
(67, 'What is the conditional of \"poder\" for \"nosotros\"?', 'Podremos', 'Podríamos', 'Podemos', 'Pudiéramos', 'b', 'hard', 'Verbs'),
(68, 'How do you form the present perfect of \"escribir\"?', 'He escribido', 'He escrito', 'Había escrito', 'Hube escrito', 'b', 'hard', 'Verbs'),
(69, 'What is the subjunctive of \"saber\" for \"tú\"?', 'Sabes', 'Sabas', 'Sepas', 'Supes', 'c', 'hard', 'Verbs'),
(70, 'Conjugate \"caber\" (to fit) for \"yo\" in present:', 'Cabo', 'Capo', 'Quepo', 'Cupo', 'c', 'hard', 'Verbs'),
(71, 'What is the past participle of \"abrir\"?', 'Abrido', 'Abierto', 'Abrito', 'Abriendo', 'b', 'hard', 'Verbs'),
(72, 'How do you say \"I would have gone\" in Spanish?', 'Habría ido', 'Hubiera ido', 'He ido', 'Había ido', 'a', 'hard', 'Verbs'),
(73, 'How do you say \"Good morning\" in Spanish?', 'Buenas noches', 'Buenas tardes', 'Buenos días', 'Buen día', 'c', 'easy', 'Phrases'),
(74, 'What does \"¿Cómo te llamas?\" mean?', 'How are you?', 'What is your name?', 'Where are you from?', 'How old are you?', 'b', 'easy', 'Phrases'),
(75, 'How do you say \"Thank you\" in Spanish?', 'Por favor', 'De nada', 'Gracias', 'Lo siento', 'c', 'easy', 'Phrases'),
(76, 'What does \"Mucho gusto\" mean?', 'Thank you', 'Nice to meet you', 'See you later', 'Excuse me', 'b', 'easy', 'Phrases'),
(77, 'How do you say \"I don\'t understand\" in Spanish?', 'No sé', 'No entiendo', 'No hablo', 'No quiero', 'b', 'easy', 'Phrases'),
(78, 'What does \"¿Dónde está el baño?\" mean?', 'What time is it?', 'How much is it?', 'Where is the bathroom?', 'What is this?', 'c', 'easy', 'Phrases'),
(79, 'How do you say \"Please\" in Spanish?', 'Gracias', 'Por favor', 'De nada', 'Perdón', 'b', 'easy', 'Phrases'),
(80, 'What does \"Hasta luego\" mean?', 'Hello', 'Good night', 'See you later', 'Welcome', 'c', 'easy', 'Phrases'),
(81, 'How do you say \"I would like...\" in Spanish?', 'Yo quiero', 'Me gustaría', 'Yo necesito', 'Yo tengo', 'b', 'medium', 'Phrases'),
(82, 'What does \"¿Qué hora es?\" mean?', 'What day is it?', 'What time is it?', 'How are you?', 'Where is it?', 'b', 'medium', 'Phrases'),
(83, 'How do you say \"I\'m sorry\" (apologizing) in Spanish?', 'Con permiso', 'Lo siento', 'Perdón', 'Disculpe', 'b', 'medium', 'Phrases'),
(84, 'What does \"Tengo hambre\" mean?', 'I am tired', 'I am thirsty', 'I am hungry', 'I am cold', 'c', 'medium', 'Phrases'),
(85, 'How do you say \"Can you help me?\" in Spanish?', '¿Puedes ayudarme?', '¿Quieres ayudarme?', '¿Debes ayudarme?', '¿Vas a ayudarme?', 'a', 'medium', 'Phrases'),
(86, 'What does \"Me duele la cabeza\" mean?', 'I have a cold', 'My head hurts', 'I feel sick', 'I am dizzy', 'b', 'medium', 'Phrases'),
(87, 'How do you say \"What do you recommend?\" in Spanish?', '¿Qué quieres?', '¿Qué recomiendas?', '¿Qué piensas?', '¿Qué necesitas?', 'b', 'medium', 'Phrases'),
(88, 'What does \"Estoy perdido/a\" mean?', 'I am tired', 'I am lost', 'I am late', 'I am confused', 'b', 'medium', 'Phrases'),
(89, 'What does \"No hay de qué\" mean?', 'There is nothing', 'You\'re welcome', 'I don\'t know', 'It doesn\'t matter', 'b', 'hard', 'Phrases'),
(90, 'How do you say \"It\'s not a big deal\" in Spanish?', 'Es muy importante', 'No es para tanto', 'Es un problema', 'No me importa', 'b', 'hard', 'Phrases'),
(91, 'What does \"Meter la pata\" mean idiomatically?', 'To put your foot in', 'To make a mistake', 'To walk away', 'To kick something', 'b', 'hard', 'Phrases'),
(92, 'How do you say \"Once in a blue moon\" in Spanish?', 'De vez en cuando', 'Cada luna azul', 'De higos a brevas', 'Una vez más', 'c', 'hard', 'Phrases'),
(93, 'What does \"Estar en las nubes\" mean?', 'To be happy', 'To be daydreaming', 'To be flying', 'To be sad', 'b', 'hard', 'Phrases'),
(94, 'How do you say \"To beat around the bush\" in Spanish?', 'Ir al grano', 'Andarse por las ramas', 'Dar en el clavo', 'Buscar el arbusto', 'b', 'hard', 'Phrases'),
(95, 'What does \"Costar un ojo de la cara\" mean?', 'To hurt your eye', 'To be very expensive', 'To be difficult', 'To see clearly', 'b', 'hard', 'Phrases'),
(96, 'How do you say \"To have a way with words\" in Spanish?', 'Tener palabras', 'Tener labia', 'Tener lengua', 'Tener voz', 'b', 'hard', 'Phrases');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `attempt_id` int NOT NULL,
  `user_id` int NOT NULL,
  `score` int NOT NULL,
  `total_questions` int NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `difficulty_level` enum('easy','medium','hard') NOT NULL,
  `category` varchar(50) NOT NULL,
  `attempt_date` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`attempt_id`, `user_id`, `score`, `total_questions`, `percentage`, `difficulty_level`, `category`, `attempt_date`) VALUES
(1, 2, 8, 8, 100.00, 'easy', 'Grammar', '2026-01-23 22:33:42'),
(2, 2, 3, 8, 37.50, 'medium', 'Grammar', '2026-01-23 22:49:05'),
(3, 2, 7, 8, 87.50, 'easy', 'Grammar', '2026-01-26 18:53:46'),
(4, 2, 4, 8, 50.00, 'medium', 'Grammar', '2026-01-26 18:58:14'),
(5, 2, 7, 8, 87.50, 'medium', 'Grammar', '2026-01-26 19:08:50'),
(6, 2, 3, 10, 30.00, 'hard', 'Mixed', '2026-02-05 14:42:34'),
(7, 2, 9, 10, 90.00, 'medium', 'Mixed', '2026-02-13 18:41:53'),
(8, 2, 6, 10, 60.00, 'hard', 'Mixed', '2026-02-24 21:04:49'),
(9, 2, 7, 8, 87.50, 'hard', 'Vocabulary', '2026-02-24 21:09:27'),
(10, 2, 0, 8, 0.00, 'hard', 'Vocabulary', '2026-02-24 21:10:15'),
(11, 2, 9, 10, 90.00, 'medium', 'Mixed', '2026-02-24 21:25:03'),
(12, 2, 5, 8, 62.50, 'hard', 'Vocabulary', '2026-03-16 16:30:22'),
(13, 2, 7, 8, 88.00, 'hard', 'Vocabulary', '2026-04-01 06:27:00'),
(14, 2, 7, 8, 88.00, 'hard', 'Vocabulary', '2026-04-01 06:27:02'),
(15, 2, 7, 8, 88.00, 'hard', 'Vocabulary', '2026-04-01 08:08:03'),
(16, 2, 7, 8, 88.00, 'hard', 'Vocabulary', '2026-04-01 08:09:20'),
(17, 2, 3, 10, 30.00, 'hard', 'Mixed', '2026-04-01 08:09:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `current_level` enum('easy','medium','hard') DEFAULT 'easy',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `current_level`, `created_at`) VALUES
(1, 'TestUser', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'easy', '2026-01-22 16:14:08'),
(2, 'TestAccount 1', 'mh23acx@herts.ac.uk', '$2y$10$t2bcazEq9OVokg1b4Rkuy.J4IPbBTgOnvAom4.dMDH9tzE55oNzG.', 'medium', '2026-01-23 22:10:58');

-- --------------------------------------------------------

--
-- Table structure for table `user_answers`
--

CREATE TABLE `user_answers` (
  `answer_id` int NOT NULL,
  `attempt_id` int NOT NULL,
  `question_id` int NOT NULL,
  `selected_answer` char(1) NOT NULL,
  `is_correct` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_answers`
--

INSERT INTO `user_answers` (`answer_id`, `attempt_id`, `question_id`, `selected_answer`, `is_correct`) VALUES
(1, 1, 25, 'b', 1),
(2, 1, 31, 'b', 1),
(3, 1, 28, 'b', 1),
(4, 1, 26, 'a', 1),
(5, 1, 30, 'c', 1),
(6, 1, 27, 'a', 1),
(7, 1, 29, 'b', 1),
(8, 1, 32, 'd', 1),
(9, 2, 35, 'b', 0),
(10, 2, 37, 'a', 0),
(11, 2, 39, 'c', 1),
(12, 2, 36, 'a', 1),
(13, 2, 38, 'b', 1),
(14, 2, 40, 'd', 0),
(15, 2, 33, 'a', 0),
(16, 2, 34, 'd', 0),
(17, 3, 31, 'b', 1),
(18, 3, 25, 'b', 1),
(19, 3, 32, 'b', 0),
(20, 3, 26, 'a', 1),
(21, 3, 28, 'b', 1),
(22, 3, 27, 'a', 1),
(23, 3, 29, 'b', 1),
(24, 3, 30, 'c', 1),
(25, 4, 35, 'c', 0),
(26, 4, 40, 'd', 0),
(27, 4, 33, 'b', 1),
(28, 4, 34, 'b', 1),
(29, 4, 36, 'a', 1),
(30, 4, 39, 'c', 1),
(31, 4, 37, 'a', 0),
(32, 4, 38, 'a', 0),
(33, 5, 33, 'b', 1),
(34, 5, 37, 'b', 1),
(35, 5, 34, 'b', 1),
(36, 5, 36, 'a', 1),
(37, 5, 40, 'b', 1),
(38, 5, 39, 'c', 1),
(39, 5, 35, 'c', 0),
(40, 5, 38, 'b', 1),
(41, 6, 90, 'd', 0),
(42, 6, 17, 'a', 0),
(43, 6, 71, 'c', 0),
(44, 6, 19, 'b', 1),
(45, 6, 89, 'd', 0),
(46, 6, 70, 'a', 0),
(47, 6, 41, 'a', 0),
(48, 6, 69, 'a', 0),
(49, 6, 47, 'b', 1),
(50, 6, 46, 'b', 1),
(51, 7, 85, 'a', 1),
(52, 7, 40, 'd', 0),
(53, 7, 15, 'b', 1),
(54, 7, 87, 'b', 1),
(55, 7, 58, 'a', 1),
(56, 7, 34, 'b', 1),
(57, 7, 84, 'c', 1),
(58, 7, 12, 'c', 1),
(59, 7, 62, 'b', 1),
(60, 7, 14, 'c', 1),
(61, 8, 71, 'a', 0),
(62, 8, 94, 'b', 1),
(63, 8, 19, 'b', 1),
(64, 8, 95, 'd', 0),
(65, 8, 47, 'b', 1),
(66, 8, 69, 'a', 0),
(67, 8, 91, 'a', 0),
(68, 8, 23, 'b', 1),
(69, 8, 44, 'b', 1),
(70, 8, 18, 'b', 1),
(71, 9, 22, 'd', 0),
(72, 9, 18, 'b', 1),
(73, 9, 20, 'c', 1),
(74, 9, 17, 'b', 1),
(75, 9, 24, 'b', 1),
(76, 9, 23, 'b', 1),
(77, 9, 19, 'b', 1),
(78, 9, 21, 'b', 1),
(79, 10, 23, 'd', 0),
(80, 10, 21, 'd', 0),
(81, 10, 24, 'd', 0),
(82, 10, 20, 'd', 0),
(83, 10, 18, 'd', 0),
(84, 10, 22, 'c', 0),
(85, 10, 19, 'a', 0),
(86, 10, 17, 'd', 0),
(87, 11, 61, 'c', 1),
(88, 11, 40, 'd', 0),
(89, 11, 11, 'b', 1),
(90, 11, 62, 'b', 1),
(91, 11, 10, 'c', 1),
(92, 11, 85, 'a', 1),
(93, 11, 63, 'b', 1),
(94, 11, 15, 'b', 1),
(95, 11, 9, 'b', 1),
(96, 11, 83, 'b', 1),
(97, 12, 18, 'a', 0),
(98, 12, 24, 'b', 1),
(99, 12, 17, 'b', 1),
(100, 12, 19, 'b', 1),
(101, 12, 22, 'd', 0),
(102, 12, 20, 'c', 1),
(103, 12, 21, 'a', 0),
(104, 12, 23, 'b', 1),
(105, 16, 18, 'b', 1),
(106, 16, 23, 'b', 1),
(107, 16, 20, 'c', 1),
(108, 16, 24, 'b', 1),
(109, 16, 21, 'b', 1),
(110, 16, 19, 'b', 1),
(111, 16, 17, 'b', 1),
(112, 16, 22, 'd', 0),
(113, 17, 46, 'c', 0),
(114, 17, 65, 'd', 0),
(115, 17, 23, 'c', 0),
(116, 17, 69, 'c', 1),
(117, 17, 89, 'a', 0),
(118, 17, 93, 'a', 0),
(119, 17, 24, 'a', 0),
(120, 17, 17, 'b', 1),
(121, 17, 91, 'a', 0),
(122, 17, 68, 'b', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `idx_difficulty` (`difficulty`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_diff_cat` (`difficulty`,`category`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_attempt_date` (`attempt_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `user_answers`
--
ALTER TABLE `user_answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `idx_attempt_id` (`attempt_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `attempt_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_answers`
--
ALTER TABLE `user_answers`
  MODIFY `answer_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_answers`
--
ALTER TABLE `user_answers`
  ADD CONSTRAINT `user_answers_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`attempt_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
