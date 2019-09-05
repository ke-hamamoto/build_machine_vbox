SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chat_sys`
--
CREATE DATABASE IF NOT EXISTS `chat_sys` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `chat_sys`;

-- --------------------------------------------------------

--
-- テーブルの構造 `ability`
--

DROP TABLE IF EXISTS `ability`;
CREATE TABLE IF NOT EXISTS `ability` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rare` int(1) NOT NULL,
  `cost` int(2) NOT NULL,
  `price` int(8) NOT NULL,
  `name` varchar(100) NOT NULL,
  `text` varchar(256) NOT NULL,
  `p_life` float NOT NULL,
  `p_sun` float NOT NULL,
  `p_str` float NOT NULL,
  `p_siz` float NOT NULL,
  `p_spd` float NOT NULL,
  `p_tec` float NOT NULL,
  `p_sit` float NOT NULL,
  `p_cns` float NOT NULL,
  `p_itg` float NOT NULL,
  `p_brv` float NOT NULL,
  `p_luc` float NOT NULL,
  `none_elem_up` float NOT NULL,
  `fire_elem_up` float NOT NULL,
  `aqua_elem_up` float NOT NULL,
  `elec_elem_up` float NOT NULL,
  `wood_elem_up` float NOT NULL,
  `aim_up` float NOT NULL,
  `cri_up` float NOT NULL,
  `mov_up` int(3) NOT NULL,
  `com_aim_up` float NOT NULL,
  `com_pow_up` float NOT NULL,
  `sun_def` float NOT NULL,
  `sun_rise` float NOT NULL,
  `pnc_str` int(3) NOT NULL,
  `pnc_siz` int(3) NOT NULL,
  `pnc_spd` int(3) NOT NULL,
  `counter` int(3) NOT NULL,
  `fire_counter` int(3) NOT NULL,
  `aqua_counter` int(3) NOT NULL,
  `elec_counter` int(3) NOT NULL,
  `wood_counter` int(3) NOT NULL,
  `fire_def` int(3) NOT NULL,
  `aqua_def` int(3) NOT NULL,
  `elec_def` int(3) NOT NULL,
  `wood_def` int(3) NOT NULL,
  `fire_pow` float NOT NULL,
  `aqua_pow` float NOT NULL,
  `elec_pow` float NOT NULL,
  `state_cnt_up` int(3) NOT NULL,
  `state_def` int(3) NOT NULL,
  `mov_eff` int(3) NOT NULL,
  `act_eff` int(3) NOT NULL,
  `wait_eff` int(3) NOT NULL,
  `pose_eff` int(3) NOT NULL,
  `shirt` int(3) NOT NULL,
  `barrier` int(3) NOT NULL,
  `auto_rcv` float NOT NULL,
  `self_rcv` int(3) NOT NULL,
  `drain` float NOT NULL,
  `elem_atk` int(3) NOT NULL,
  `no_mov_atk` int(3) NOT NULL,
  `avd_bns` int(3) NOT NULL,
  `def_bns` int(3) NOT NULL,
  `salary` int(3) NOT NULL,
  `act_ptn_bns` int(3) NOT NULL,
  `in_atk` int(3) NOT NULL,
  `out_atk` int(3) NOT NULL,
  `berserk` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8;

--
-- テーブルのデータのダンプ `ability`
--

REPLACE INTO `ability` (`id`, `rare`, `cost`, `price`, `name`, `text`, `p_life`, `p_sun`, `p_str`, `p_siz`, `p_spd`, `p_tec`, `p_sit`, `p_cns`, `p_itg`, `p_brv`, `p_luc`, `none_elem_up`, `fire_elem_up`, `aqua_elem_up`, `elec_elem_up`, `wood_elem_up`, `aim_up`, `cri_up`, `mov_up`, `com_aim_up`, `com_pow_up`, `sun_def`, `sun_rise`, `pnc_str`, `pnc_siz`, `pnc_spd`, `counter`, `fire_counter`, `aqua_counter`, `elec_counter`, `wood_counter`, `fire_def`, `aqua_def`, `elec_def`, `wood_def`, `fire_pow`, `aqua_pow`, `elec_pow`, `state_cnt_up`, `state_def`, `mov_eff`, `act_eff`, `wait_eff`, `pose_eff`, `shirt`, `barrier`, `auto_rcv`, `self_rcv`, `drain`, `elem_atk`, `no_mov_atk`, `avd_bns`, `def_bns`, `salary`, `act_ptn_bns`, `in_atk`, `out_atk`, `berserk`) VALUES
(1, 1, 1, 800, '体力アップ小', '体力２０％アップ', 0.2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2, 1, 1, 800, '体力アップ小', '体力２０％アップ', 0.2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

--
-- テーブルの構造 `app_list`
--

DROP TABLE IF EXISTS `app_list`;
CREATE TABLE IF NOT EXISTS `app_list` (
  `token` varchar(70) NOT NULL,
  `mode` varchar(15) NOT NULL,
  `uid` varchar(100) NOT NULL,
  `pass` varchar(100) NOT NULL,
  `mail` text NOT NULL,
  `ip` varchar(128) NOT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `bgm`
--

DROP TABLE IF EXISTS `bgm`;
CREATE TABLE IF NOT EXISTS `bgm` (
  `bgmid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(100) NOT NULL,
  `mode` varchar(10) NOT NULL,
  `type` varchar(16) NOT NULL,
  `url` text NOT NULL,
  `name` varchar(100) NOT NULL,
  `used` int(8) NOT NULL,
  PRIMARY KEY (`bgmid`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;

--
-- テーブルのデータのダンプ `bgm`
--

REPLACE INTO `bgm` (`bgmid`, `uid`, `mode`, `type`, `url`, `name`, `used`) VALUES
(1, '-', 'battle', '渋い', 'bgm/wenkamui', 'サンプル１', 0),
(2, '-', 'dngn', '切ない', 'bgm/wen-kamuy2', 'サンプル２', 0),
(3, '-', 'ev', 'ダーク', 'bgm/yomi2_mixv2', 'サンプル３', 0);

-- --------------------------------------------------------

--
-- テーブルの構造 `b_list`
--

DROP TABLE IF EXISTS `b_list`;
CREATE TABLE IF NOT EXISTS `b_list` (
  `uid` varchar(100) NOT NULL,
  `ip` varchar(128) NOT NULL,
  `token` varchar(50) NOT NULL,
  `miss_cnt` int(1) NOT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`ip`,`uid`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `consume`
--

DROP TABLE IF EXISTS `consume`;
CREATE TABLE IF NOT EXISTS `consume` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rare` int(1) NOT NULL,
  `price` int(8) NOT NULL,
  `name` varchar(100) NOT NULL,
  `text` varchar(256) NOT NULL,
  `p_life` float NOT NULL,
  `p_sun` float NOT NULL,
  `init` varchar(8) NOT NULL,
  `used` int(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

--
-- テーブルのデータのダンプ `consume`
--

REPLACE INTO `consume` (`id`, `rare`, `price`, `name`, `text`, `p_life`, `p_sun`, `init`, `used`) VALUES
(1, 1, 750, 'ライフバーガー', '体力の１０％回復する', 0.1, 0, '', 0);

-- --------------------------------------------------------

--
-- テーブルの構造 `daily_shop`
--

DROP TABLE IF EXISTS `daily_shop`;
CREATE TABLE IF NOT EXISTS `daily_shop` (
  `skill` varchar(25) NOT NULL,
  `ability` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータのダンプ `daily_shop`
--

REPLACE INTO `daily_shop` (`skill`, `ability`) VALUES
('1,2,3', '1,2');

-- --------------------------------------------------------

--
-- テーブルの構造 `item`
--

DROP TABLE IF EXISTS `item`;
CREATE TABLE IF NOT EXISTS `item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rare` int(1) NOT NULL,
  `price` int(9) NOT NULL,
  `type` varchar(8) NOT NULL,
  `name` varchar(64) NOT NULL,
  `text` varchar(64) NOT NULL,
  `used` int(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8;

--
-- テーブルのデータのダンプ `item`
--

REPLACE INTO `item` (`id`, `rare`, `price`, `type`, `name`, `text`, `used`) VALUES
(1, 1, 10, '鉱物', '石ころ', '※ロールプレイ用アイテム', 0);

-- --------------------------------------------------------

--
-- テーブルの構造 `mail_box`
--

DROP TABLE IF EXISTS `mail_box`;
CREATE TABLE IF NOT EXISTS `mail_box` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(16) NOT NULL,
  `content` text NOT NULL,
  `from_uid` varchar(100) NOT NULL,
  `to_uid` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `to_uid` (`to_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `room_list`
--

DROP TABLE IF EXISTS `room_list`;
CREATE TABLE IF NOT EXISTS `room_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(70) NOT NULL,
  `rm_uid` varchar(100) NOT NULL,
  `dbname` varchar(70) NOT NULL,
  `rname` varchar(100) NOT NULL,
  `cnt` int(4) NOT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`rm_uid`),
  KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `skill`
--

DROP TABLE IF EXISTS `skill`;
CREATE TABLE IF NOT EXISTS `skill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rare` int(1) NOT NULL,
  `cost` int(2) NOT NULL,
  `price` int(8) NOT NULL,
  `name` varchar(100) NOT NULL,
  `text` varchar(256) NOT NULL,
  `type` varchar(16) NOT NULL,
  `elem` varchar(16) NOT NULL,
  `panel_x` varchar(128) NOT NULL,
  `panel_y` varchar(128) NOT NULL,
  `atk` varchar(16) NOT NULL,
  `aim` varchar(16) NOT NULL,
  `avd` varchar(16) NOT NULL,
  `p_pow` float NOT NULL,
  `p_hit` float NOT NULL,
  `p_cri` float NOT NULL,
  `delay` int(3) NOT NULL,
  `state_normal` float NOT NULL,
  `state_yakedo` float NOT NULL,
  `state_awa` float NOT NULL,
  `state_mahi` float NOT NULL,
  `state_doku` float NOT NULL,
  `state_ice` float NOT NULL,
  `state_plant` float NOT NULL,
  `state_sleep` float NOT NULL,
  `state_anger` float NOT NULL,
  `state_sex` float NOT NULL,
  `state_fear` float NOT NULL,
  `state_strong` float NOT NULL,
  `state_dear` float NOT NULL,
  `state_heart` float NOT NULL,
  `state_cool` float NOT NULL,
  `state_inferno` float NOT NULL,
  `change_none` float NOT NULL,
  `change_fire` float NOT NULL,
  `change_aqua` float NOT NULL,
  `change_elec` float NOT NULL,
  `change_wood` float NOT NULL,
  `fire_bad_state` float NOT NULL,
  `aqua_bad_state` float NOT NULL,
  `elec_bad_state` float NOT NULL,
  `break` int(3) NOT NULL,
  `reset_barrier` int(3) NOT NULL,
  `reset_shirt` int(3) NOT NULL,
  `atk_delay` int(3) NOT NULL,
  `break_pose` int(3) NOT NULL,
  `hit_away` int(3) NOT NULL,
  `atk_dmg` float NOT NULL,
  `push` int(3) NOT NULL,
  `pull` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=501 DEFAULT CHARSET=utf8;

--
-- テーブルのデータのダンプ `skill`
--

REPLACE INTO `skill` (`id`, `rare`, `cost`, `price`, `name`, `text`, `type`, `elem`, `panel_x`, `panel_y`, `atk`, `aim`, `avd`, `p_pow`, `p_hit`, `p_cri`, `delay`, `state_normal`, `state_yakedo`, `state_awa`, `state_mahi`, `state_doku`, `state_ice`, `state_plant`, `state_sleep`, `state_anger`, `state_sex`, `state_fear`, `state_strong`, `state_dear`, `state_heart`, `state_cool`, `state_inferno`, `change_none`, `change_fire`, `change_aqua`, `change_elec`, `change_wood`, `fire_bad_state`, `aqua_bad_state`, `elec_bad_state`, `break`, `reset_barrier`, `reset_shirt`, `atk_delay`, `break_pose`, `hit_away`, `atk_dmg`, `push`, `pull`) VALUES
(1, 1, 1, 800, 'ノーマルパンチ', '強烈なパンチ', 'cmd', '無', '0 -1 1 0', '-1 0 0 1', 'cmd', 'tec', 'spd', 0, 0.1, 0.35, 16, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2, 1, 1, 1000, 'ファイアパンチ', '炎をまとうパンチ', 'cmd', '火', '0 -1 1 0', '-1 0 0 1', 'cmd', 'tec', 'spd', -0.05, 0.1, 0.35, 16, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3, 1, 1, 1000, 'アクアパンチ', '水をまとうパンチ', 'cmd', '水', '0 -1 1 0', '-1 0 0 1', 'cmd', 'tec', 'spd', -0.05, 0.1, 0.35, 16, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- テーブルの構造 `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `uid` varchar(100) NOT NULL,
  `token` varchar(70) NOT NULL,
  `pass` char(100) NOT NULL,
  `mail` text NOT NULL,
  `join_list` text NOT NULL,
  `img_name` varchar(40) NOT NULL,
  `img` text NOT NULL,
  `myjoin` varchar(32) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータのダンプ `user`
--

REPLACE INTO `user` (`uid`, `token`, `pass`, `mail`, `join_list`, `img_name`, `img`, `myjoin`) VALUES
('test1', '201905040855185473NnKY8rqn', 'dcddb75469b4b4875094e14561e573d8', '', '', '', '', ''),
('test2', '20190427234813353578RsZVND', 'dcddb75469b4b4875094e14561e573d8', '', '', '', '', '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
