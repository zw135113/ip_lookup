# 环境

- Ubuntu 2204
- PHP 8.1
- Nginx 1.18.0

# 部署过程

## 部署web环境

### 部署PHP

```bash
apt -y install php-fpm php-mysql php-json php-gd php-xml php-mbstring php-zip php-curl
```

### 部署nginx

```bash
apt -y install nginx
```

## 克隆项目到本地

```bash
git clone https://gitee.com/zw135113/ip_lookup.git
```


## ngixn配置文件示例

**/etc/nginx/sites-enabled/ip_lookup.conf** 

```
server {
    listen 80;
    server_name 10.0.0.200;

    root /apps/ip_lookup;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }

}
```

**加载配置文件**

```bash
nginx -s reload
```

## 效果展示

![输入图片说明](https://foruda.gitee.com/images/1696992305855207243/f2aa2ccc_9259835.png "1696991102529.png")

