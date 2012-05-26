#include <stdlib.h>
#include <stdio.h>
#include <glib.h>
#include <curl/curl.h>

#include "utils.h"

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
