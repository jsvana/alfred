PROG = alfred.rpc
OBJS = main.o json.o error.o sql.o
MODULES = alfred.o location.o network.o password.o xbmc.o
LIBS = json glib-2.0

CC = gcc
CFLAGS = -std=c99 -ggdb -Wall -ggdb $(shell pkg-config --cflags ${LIBS}) $(shell mysql_config --cflags)
LDFLAGS = $(shell pkg-config --libs ${LIBS}) $(shell mysql_config --libs) -Wl,-rpath -Wl,/usr/local/lib -lcrypto

all: ${PROG}

${PROG}: ${OBJS} ${MODULES}
	@echo ${CC} -o ${PROG}
	@${CC} ${CFLAGS} -o $@ $+ ${LDFLAGS}

%.o: %.c
	@echo ${CC} -c $<
	@${CC} ${CFLAGS} -c $< -o $@

run: all
	./${PROG}

clean:
	rm -f ${PROG} ${OBJS} ${MODULES}

.PHONY: all clean install
