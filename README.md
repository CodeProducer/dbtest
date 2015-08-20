# dbtest
You can create, delete truncate json tables.
Add, update, delete entries.
View transaction log. Use /log.php  to see the log.
You can see only new values in trnsaction logs.
All uncommited changes are added to tmp log,
after transaction have been successfully commit this log moves to the log.log.


Dir "/db" and dir "/log" should be accessed by the php user to read and write files there.
Th simple example expects that simple table 'test' exists use "/init.php" to create it.

