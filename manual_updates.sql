alter table categories add status_id int(10) after category_name not null default 0;
alter table service_packages change service_id category_id int(11) not null ;
alter table service_package_details change service_detail_id service_package_id int(10) not null;
alter table service_package_details add description varchar(255) not null after service_package_id;
alter table service_package_details modify media_data json null comment 'sample {"media_url":"...", "media_type":"[video|audio|image]", "size":"xMB"}';
alter table statuses add status_category_id int(10) not null default 0;
