#ifndef ALFRED_SQL
#define ALFRED_SQL

#include <mysql.h>

#define ALFRED_SQL_USER "alfred"
#define ALFRED_SQL_PASS "my_cocaine"
#define ALFRED_SQL_HOST "localhost"
#define ALFRED_SQL_PORT 3306
#define ALFRED_SQL_DB "alfred"

void alfred_sql_init();
void alfred_sql_shutdown();
char *alfred_sql_escape_string(const char *data);
MYSQL *alfred_sql_connection();

#endif
