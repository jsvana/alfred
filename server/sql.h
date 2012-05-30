#ifndef ALFRED_SQL
#define ALFRED_SQL

#include <mysql.h>

void alfred_sql_init();
void alfred_sql_shutdown();
char *alfred_sql_escape_string(const char *data);
MYSQL *alfred_sql_connection();

#endif
