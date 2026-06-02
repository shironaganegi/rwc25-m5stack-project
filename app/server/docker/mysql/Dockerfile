FROM mysql:8.4-oracle

RUN mkdir /var/log/mysql
RUN chown mysql:mysql /var/log/mysql

# 文字セット設定を追加
RUN echo "[mysqld]" >> /etc/mysql/conf.d/charset.cnf && \
    echo "character-set-server=utf8mb4" >> /etc/mysql/conf.d/charset.cnf && \
    echo "collation-server=utf8mb4_unicode_ci" >> /etc/mysql/conf.d/charset.cnf && \
    echo "init_connect='SET NAMES utf8mb4'" >> /etc/mysql/conf.d/charset.cnf

RUN echo "[client]" >> /etc/mysql/conf.d/charset.cnf && \
    echo "default-character-set=utf8mb4" >> /etc/mysql/conf.d/charset.cnf
