alter table categories add status_id int(10) after category_name not null default 0;
alter table service_packages change service_id category_id int(11) not null ;