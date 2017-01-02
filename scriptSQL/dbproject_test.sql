-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 02, 2017 at 08:52 PM
-- Server version: 5.7.16-0ubuntu0.16.04.1
-- PHP Version: 7.0.8-0ubuntu0.16.04.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbproject_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `AIMER`
--

CREATE TABLE `AIMER` (
  `IDTWEET` int(11) NOT NULL,
  `IDUSER` int(11) NOT NULL,
  `NOTIF` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `CONCERNER`
--

CREATE TABLE `CONCERNER` (
  `IDTWEET` int(11) NOT NULL,
  `IDHASHTAGS` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `HASHTAGS`
--

CREATE TABLE `HASHTAGS` (
  `IDHASHTAGS` int(11) NOT NULL,
  `NAME` char(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `MENTIONNER`
--

CREATE TABLE `MENTIONNER` (
  `IDTWEET` int(11) NOT NULL,
  `IDUSER` int(11) NOT NULL,
  `NOTIF` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `SUIVRE`
--

CREATE TABLE `SUIVRE` (
  `IDUSER` int(11) NOT NULL,
  `IDUSER_1` int(11) NOT NULL,
  `NOTIF` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `TWEET`
--

CREATE TABLE `TWEET` (
  `IDTWEET` int(11) NOT NULL,
  `IDUSER` int(11) NOT NULL,
  `IDTWEET_REPONSE` int(11) DEFAULT NULL,
  `CONTENU` char(140) NOT NULL,
  `DATEPUBLI` char(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `UTILISATEUR`
--

CREATE TABLE `UTILISATEUR` (
  `id` int(11) NOT NULL,
  `username` char(32) DEFAULT NULL,
  `name` char(32) DEFAULT NULL,
  `password` char(150) NOT NULL,
  `email` char(32) DEFAULT NULL,
  `avatar` char(32) DEFAULT NULL,
  `inscri` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `AIMER`
--
ALTER TABLE `AIMER`
  ADD PRIMARY KEY (`IDTWEET`,`IDUSER`),
  ADD KEY `FK_AIMER_UTILISATEUR` (`IDUSER`);

--
-- Indexes for table `CONCERNER`
--
ALTER TABLE `CONCERNER`
  ADD PRIMARY KEY (`IDTWEET`,`IDHASHTAGS`),
  ADD KEY `FK_CONCERNER_HASHTAGS` (`IDHASHTAGS`);

--
-- Indexes for table `HASHTAGS`
--
ALTER TABLE `HASHTAGS`
  ADD PRIMARY KEY (`IDHASHTAGS`);

--
-- Indexes for table `MENTIONNER`
--
ALTER TABLE `MENTIONNER`
  ADD PRIMARY KEY (`IDTWEET`,`IDUSER`),
  ADD KEY `FK_MENTIONNER_UTILISATEUR` (`IDUSER`);

--
-- Indexes for table `SUIVRE`
--
ALTER TABLE `SUIVRE`
  ADD PRIMARY KEY (`IDUSER`,`IDUSER_1`),
  ADD KEY `FK_SUIVRE_UTILISATEUR1` (`IDUSER_1`);

--
-- Indexes for table `TWEET`
--
ALTER TABLE `TWEET`
  ADD PRIMARY KEY (`IDTWEET`),
  ADD KEY `FK_TWEET_UTILISATEUR` (`IDUSER`),
  ADD KEY `FK_TWEET_TWEET` (`IDTWEET_REPONSE`);

--
-- Indexes for table `UTILISATEUR`
--
ALTER TABLE `UTILISATEUR`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `HASHTAGS`
--
ALTER TABLE `HASHTAGS`
  MODIFY `IDHASHTAGS` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `TWEET`
--
ALTER TABLE `TWEET`
  MODIFY `IDTWEET` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `UTILISATEUR`
--
ALTER TABLE `UTILISATEUR`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `AIMER`
--
ALTER TABLE `AIMER`
  ADD CONSTRAINT `AIMER_ibfk_1` FOREIGN KEY (`IDTWEET`) REFERENCES `TWEET` (`IDTWEET`),
  ADD CONSTRAINT `AIMER_ibfk_2` FOREIGN KEY (`IDUSER`) REFERENCES `UTILISATEUR` (`id`);

--
-- Constraints for table `CONCERNER`
--
ALTER TABLE `CONCERNER`
  ADD CONSTRAINT `CONCERNER_ibfk_1` FOREIGN KEY (`IDTWEET`) REFERENCES `TWEET` (`IDTWEET`),
  ADD CONSTRAINT `CONCERNER_ibfk_2` FOREIGN KEY (`IDHASHTAGS`) REFERENCES `HASHTAGS` (`IDHASHTAGS`);

--
-- Constraints for table `MENTIONNER`
--
ALTER TABLE `MENTIONNER`
  ADD CONSTRAINT `MENTIONNER_ibfk_1` FOREIGN KEY (`IDTWEET`) REFERENCES `TWEET` (`IDTWEET`),
  ADD CONSTRAINT `MENTIONNER_ibfk_2` FOREIGN KEY (`IDUSER`) REFERENCES `UTILISATEUR` (`id`);

--
-- Constraints for table `SUIVRE`
--
ALTER TABLE `SUIVRE`
  ADD CONSTRAINT `SUIVRE_ibfk_1` FOREIGN KEY (`IDUSER`) REFERENCES `UTILISATEUR` (`id`),
  ADD CONSTRAINT `SUIVRE_ibfk_2` FOREIGN KEY (`IDUSER_1`) REFERENCES `UTILISATEUR` (`id`);

--
-- Constraints for table `TWEET`
--
ALTER TABLE `TWEET`
  ADD CONSTRAINT `TWEET_ibfk_1` FOREIGN KEY (`IDUSER`) REFERENCES `UTILISATEUR` (`id`),
  ADD CONSTRAINT `TWEET_ibfk_2` FOREIGN KEY (`IDTWEET_REPONSE`) REFERENCES `TWEET` (`IDTWEET`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
