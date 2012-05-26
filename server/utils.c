#include <stdlib.h>
#include <stdio.h>
#include <glib.h>
#include <curl/curl.h>
#include <mysql.h>

#include "utils.h"
#include "sql.h"

// Store the key for some magic
const char *auth_key = NULL;
// Will be 1 on true, 0 on false
int authed = -1;

char *alfred_utils_md5(const char *data) {
	return g_compute_checksum_for_string(G_CHECKSUM_MD5, data, -1);
}

char *alfred_utils_curl(const char *url) {
	return alfred_utils_curl_post(url, NULL);
}

char *alfred_utils_curl_post(const char *url, const char *post_data) {
	CURL *handle;
	FILE *file;
	char *data;
	size_t n;
	int ret;

	file = open_memstream(&data, &n);
	handle = curl_easy_init();

	// Where are we going?
	curl_easy_setopt(handle, CURLOPT_URL, url);

	// Info to pass to the callback
	curl_easy_setopt(handle, CURLOPT_WRITEDATA, file);

	// Set a user-agent
	curl_easy_setopt(handle, CURLOPT_USERAGENT, "alfred/libcurl/1.0");

	// If we have data, use it
	if (post_data) {
		curl_easy_setopt(handle, CURLOPT_POSTFIELDS, post_data);
	}

	// Here we go!
	ret = curl_easy_perform(handle);

	fclose(file);
	if (ret) {
		free(data);
		data = NULL;
	}

	curl_easy_cleanup(handle);

	return data;
}

void alfred_auth_set_key(const char *key) {
	auth_key = key;
}

void alfred_auth_forget_key() {
	auth_key = NULL;
}

int alfred_authed() {
	if (authed != -1) {
		return authed;
	}
	MYSQL *conn = alfred_sql_connection();
	char *escaped_key = alfred_sql_escape_string(auth_key);
	
	char *query = g_strconcat("UPDATE `sessions` SET `expiration`=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE `api_key`='", escaped_key, "' AND `expiration`>NOW() LIMIT 1;", NULL);

	// TODO: Check error
	mysql_query(conn, query);
	my_ulonglong rows = mysql_affected_rows(conn);

	g_free(escaped_key);
	g_free(query);

	return authed = (rows == 1);
}
