# Shovel

**Yeah, don't use this...**

A tool to shovel data from one place to another.

Shovel is written in PHP, because that's what you do. It's a command-line utility that tries to make it braindead simple to move data from one table in one database server to... another table... in another database server. 

Story time:

> Let's say you're a baller and you shelled out dat moolah for Oracle 12c, 11a, 11g, 4G, LTE, wahtever... and now some freaking plebs don't wanna pay up but they want your data. So they use this tool to pipe all that Oracle crap over to MariaDB (until it's bought by Oracle or whatever, you know).

Story time is over, fools.

## Usage 

Well... you're going to make these YAML files becuase that's what you do. They're going to look super special too and say a lot. Something like this:

```yaml
# official-to-trendy.yaml
src:
  driver: oracle
  host: something.official.as.fk
  database: xe
  username: ohlawd
  password: ohl$wd?
  
dest:
  driver: mariadb
  host: something.trendy.as.fk
  database: ohlawd
  username: mustache
  password: mu$tac$e!

tables:
  - PS_NC_HELLO_WORLD
  - PS_NC_HELLO_CONTINENT
  - PS_NC_HELLO_COUNTRY

transforms:
  - SomeClassNameInPipeline
```

So once we describe the pull we want to do (by specifying source and destination servers, tables to mirror and any transformations to apply), we can run the pull like this:

```bash
$ bin/shovel dig official-to-trendy.yaml
```

Digging does the following to be super helpful to us because it's a cool buddy:

- If tables do not exist in the destination database, they will be created with a schema matching the source.
- If the number of rows in the source are below destination beyond some threshold, complaints are filed.
- If all is well, build some fancy progress meter shit.
- Pick a chunk of rows in source table, insert into destination.
- Slack shit.