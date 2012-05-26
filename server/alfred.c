#include <string.h>
#include <json.h>
#include <mysql.h>
#include <glib.h>

#include <openssl/md5.h>

#include "alfred.h"
#include "sql.h"
#include "json.h"
#include "error.h"

void alfred_module_alfred(const char *command, json_object *params) {
	if (strcmp(command, "Login") == 0) {
		char *username = alfred_json_get_string(params, "username");
		char *password = alfred_json_get_string(params, "password");
		char *escaped_username = alfred_sql_escape_string(username);
		char *escaped_password = alfred_sql_escape_string(password);
		g_free(username);
		g_free(password);

		// TODO: Check for params

		MYSQL *conn = alfred_sql_connection();
		char *query = g_strconcat("SELECT `username` FROM `users` WHERE `username`='", escaped_username, "' AND `password`=MD5('", escaped_password, "') LIMIT 1;", NULL);
		mysql_query(conn, query);
		MYSQL_RES *res = mysql_store_result(conn);
		int num = mysql_num_rows(res);
		mysql_free_result(res);
		g_free(query);

		if (num == 1) {
			char *key = g_strdup_printf("%s%s%zd", escaped_username, escaped_password, time(NULL));
			GChecksum *hasher = g_checksum_new(G_CHECKSUM_MD5);
			g_checksum_update(hasher, (unsigned char *)key, -1);
			unsigned char *hash = g_checksum_get_string(hasher);

			query = g_strconcat("INSERT INTO `sessions` (api_key, expiration) VALUES ('", hash, "', DATE_ADD(NOW(), INTERVAL 1 HOUR));", NULL);

			mysql_query(conn, query);
			// TODO: Check affected rows

			json_object *obj = json_object_new_object();
			json_object *key_obj = json_object_new_string(hash);
			json_object_object_add(obj, "key", key_obj);

			alfred_json_create_display(0, "Method success", obj);

			g_checksum_free(hasher);
			g_free(key);
			g_free(query);
		} else {
			alfred_error_static(ALFRED_ERROR_METHOD_FAILED);
		}

		g_free(escaped_username);
		g_free(escaped_password);
	}
}
