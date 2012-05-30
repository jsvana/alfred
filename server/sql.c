#include <stdlib.h>
#include <string.h>
#include <mysql.h>
#include <glib.h>

#include "sql.h"
#include "config.h"

MYSQL *sql = NULL;

void alfred_sql_init() {
	sql = mysql_init(NULL);
	
	// TODO: Check on error
	sql = mysql_real_connect(sql, ALFRED_SQL_HOST, ALFRED_SQL_USER, ALFRED_SQL_PASS, ALFRED_SQL_DB, ALFRED_SQL_PORT, NULL, 0);
}

MYSQL *alfred_sql_connection() {
	if (!sql) {
		alfred_sql_init();
	}

	return sql;
}

void alfred_sql_shutdown() {
	if (sql) {
		mysql_close(sql);
	}
}

char *alfred_sql_escape_string(const char *data) {
	int len = strlen(data);
	char *str = g_malloc(sizeof(char) + ((2 * len) + 1));

	// Note: we could get the length from here and return it too
	mysql_real_escape_string(alfred_sql_connection(), str, data, len);

	return str;
}
