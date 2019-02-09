alter table categories add status_id int(10) after category_name not null default 0;
alter table service_packages change service_id category_id int(11) not null ;
alter table service_package_details change service_detail_id service_package_id int(10) not null;
alter table service_package_details add description varchar(255) not null after service_package_id;
alter table service_package_details modify media_data json null comment 'sample {"media_url":"...", "media_type":"[video|audio|image]", "size":"xMB"}';
alter table statuses add status_category_id int(10) not null default 0;
alter table service_providers add work_location varchar(45) null after service_provider_name;
alter table experts drop work_location;
alter table service_providers add work_lat varchar(45) null after work_location;
alter table experts drop work_lat;
alter table service_providers add work_lng varchar(45) null after work_lat;
alter table experts drop work_lng;
alter table service_providers add status_id varchar(45) null after work_lng;
alter table experts drop status_id;
alter table service_providers add overall_rating after status_id;
alter table experts drop overall_rating;
alter table service_providers add overall_likes varchar(45) null after overall_rating;
alter table experts drop overall_likes;
alter table service_providers add overall_dislikes varchar(45) null after overall_likes;
alter table experts drop overall_dislikes;
alter table experts add user_id varchar(45) null after id;



