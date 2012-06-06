#ifndef ALFRED_UTILS
#define ALFRED_UTILS

char *alfred_utils_md5(const char *data);
char *alfred_utils_curl(const char *url);
char *alfred_utils_curl_post(const char *url, const char *data);
char *alfred_utils_curl_post_login(const char *url, const char *data, const char *user, const char *pass);

void alfred_auth_set_key(const char *key);
void alfred_auth_forget_key();
int alfred_authed();

#endif
