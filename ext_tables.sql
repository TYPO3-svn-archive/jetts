#
# fields used by the Typoscript template selector
#
CREATE TABLE pages (
    tx_jetts_template tinytext NOT NULL
);
CREATE TABLE pages (
    tx_jetts_subtemplate tinytext NOT NULL
);
#
# fields used by the mapping wizard
#
CREATE TABLE pages (
    tx_jetts_template_mapping tinytext NOT NULL
);
CREATE TABLE pages (
    tx_jetts_subtemplate_mapping tinytext NOT NULL
);

#
# Table structure for table 'tx_jetts_mapping'
#
CREATE TABLE tx_jetts_mapping (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    title tinytext,
    description text,
    thumbnail text,
    html text,
    llxml text,
    mapping text,
    mapping_json text,
    notes text,
    work_on_subpart tinytext,
    ts_override tinytext,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);