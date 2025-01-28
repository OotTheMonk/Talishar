-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 03, 2022 at 02:11 AM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.1.2
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
CREATE TABLE `completedgame` (
  `CompletionTime` timestamp NOT NULL DEFAULT current_timestamp(),
  `WinningHero` char(6) NOT NULL,
  `LosingHero` char(6) NOT NULL,
  `WinningPID` int(11) DEFAULT NULL,
  `LosingPID` int(11) DEFAULT NULL,
  `WinnerHealth` int(11) DEFAULT NULL,
  `FirstPlayer` tinyint(4) DEFAULT NULL,
  `NumTurns` int(11) NOT NULL,
  `Format` int(11) DEFAULT NULL,
  `GameID` int(22) NOT NULL,
  `WinnerDeck` varchar(1000) DEFAULT NULL,
  `LoserDeck` varchar(1000) DEFAULT NULL,
  `lastAuthKey` varchar(128) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `favoritedeck`
--

CREATE TABLE `favoritedeck` (
  `decklink` varchar(128) NOT NULL,
  `usersId` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `hero` varchar(8) NOT NULL,
  `format` varchar(32) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `pwdreset`
--

CREATE TABLE `pwdreset` (
  `pwdResetId` int(11) NOT NULL,
  `pwdResetEmail` text NOT NULL,
  `pwdResetSelector` text NOT NULL,
  `pwdResetToken` longtext NOT NULL,
  `pwdResetExpires` text NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `usersId` int(11) NOT NULL,
  `usersUid` varchar(128) NOT NULL,
  `usersEmail` varchar(128) NOT NULL,
  `usersPwd` varchar(128) NOT NULL,
  `rememberMeToken` varchar(64) DEFAULT NULL,
  `patreonAccessToken` varchar(64) DEFAULT NULL,
  `patreonRefreshToken` varchar(64) DEFAULT NULL,
  `fabraryId` varchar(64) DEFAULT NULL,
  `fabdbId` varchar(64) DEFAULT NULL,
  `patreonEnum` varchar(64) DEFAULT NULL,
  `isBanned` tinyint(1) NOT NULL DEFAULT 0,
  `lastLoggedIP` varchar(32) DEFAULT NULL,
  `lastGameName` int(11) NOT NULL DEFAULT 0,
  `lastPlayerId` int(11) NOT NULL DEFAULT 0,
  `lastAuthKey` varchar(128) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
--
-- Indexes for dumped tables
--

--
-- Indexes for table `completedgame`
--
ALTER TABLE `completedgame`
ADD PRIMARY KEY (`GameID`),
  ADD KEY `FK_WINNING_PLAYER` (`WinningPID`),
  ADD KEY `FK_LOSING_PLAYER` (`LosingPID`);
--
-- Indexes for table `favoritedeck`
--
ALTER TABLE `favoritedeck`
ADD PRIMARY KEY (`decklink`),
  ADD KEY `usersId` (`usersId`);
--
-- Indexes for table `pwdreset`
--
ALTER TABLE `pwdreset`
ADD PRIMARY KEY (`pwdResetId`);
--
-- Indexes for table `users`
--
ALTER TABLE `users`
ADD PRIMARY KEY (`usersId`),
  ADD KEY `usersUid` (`usersUid`);
--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `completedgame`
--
ALTER TABLE `completedgame`
MODIFY `GameID` int(22) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 96;
--
-- AUTO_INCREMENT for table `pwdreset`
--
ALTER TABLE `pwdreset`
MODIFY `pwdResetId` int(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 2;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `usersId` int(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 5;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `completedgame`
--
ALTER TABLE `completedgame`
ADD CONSTRAINT `FK_LOSING_PLAYER` FOREIGN KEY (`LosingPID`) REFERENCES `users` (`usersId`),
  ADD CONSTRAINT `FK_WINNING_PLAYER` FOREIGN KEY (`WinningPID`) REFERENCES `users` (`usersId`);
COMMIT;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
CREATE TABLE `badges` (
  `badgeId` int(11) NOT NULL,
  `topText` varchar(128) DEFAULT NULL,
  `bottomText` varchar(128) DEFAULT NULL,
  `image` varchar(128) DEFAULT NULL,
  `link` varchar(256) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`badgeId`, `topText`, `bottomText`, `image`)
VALUES (
    1,
    'Commotion #1',
    'Wins: {0}',
    './concat/WTR175.webp'
  );
--
-- Indexes for dumped tables
--

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
ADD PRIMARY KEY (`badgeId`);
--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
MODIFY `badgeId` int(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 2;
COMMIT;
CREATE TABLE `playerbadge` (
  `playerId` int(11) NOT NULL,
  `badgeId` int(11) NOT NULL,
  `intVariable` int(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
--
-- Dumping data for table `playerbadge`
--

--
-- Indexes for table `playerbadge`
--
ALTER TABLE `playerbadge`
ADD PRIMARY KEY (`playerId`, `badgeId`);
CREATE TABLE `challengeresult` (
  `gameId` int(11) NOT NULL,
  `challengeId` int(11) NOT NULL,
  `playerId` int(11) NOT NULL,
  `result` int(11) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
ALTER TABLE `challengeresult`
ADD PRIMARY KEY (`gameId`, `playerId`) USING BTREE,
  ADD KEY `PLAYER_ID_INDEX` (`playerId`);
ALTER TABLE `challengeresult`
ADD CONSTRAINT `FK_GAME_ID` FOREIGN KEY (`gameId`) REFERENCES `completedgame` (`GameID`);
CREATE TABLE `savedsettings` (
  `playerId` int(11) NOT NULL,
  `settingNumber` int(11) NOT NULL,
  `settingValue` int(11) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
ALTER TABLE `savedsettings`
ADD PRIMARY KEY (`playerId`, `settingNumber`);


--
-- Table structure for table `carddefinition`
--

CREATE TABLE `carddefinition` (
  `cardID` varchar(16) NOT NULL,
  `hasGoAgain` int(11) NOT NULL DEFAULT 0,
  `playAbility` varchar(512) DEFAULT NULL,
  `hitEffect` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for table `carddefinition`
--
ALTER TABLE `carddefinition`
  ADD PRIMARY KEY (`cardID`);

--
-- Table structure for table `blocklist`
--
CREATE TABLE `blocklist` (
  `blockingPlayer` int(11) NOT NULL,
  `blockedPlayer` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for table `blocklist`
--
ALTER TABLE `blocklist`
  ADD PRIMARY KEY (`blockingPlayer`,`blockedPlayer`),
  ADD KEY `blockingPlayer` (`blockingPlayer`);

SET GLOBAL event_scheduler = ON;

CREATE EVENT AutoDeleteOldCompletedGames IF NOT EXISTS
ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 1 DAY 
ON COMPLETION PRESERVE
DO
DELETE LOW_PRIORITY FROM completedgame WHERE CompletionTime < DATE_SUB(NOW(), INTERVAL 90 DAY);
