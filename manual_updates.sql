alter table categories add status_id int(10) not null default 0 after category_name;
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
alter table service_providers add overall_rating float(10,2) default 0.0 after status_id;
alter table experts drop overall_rating;
alter table service_providers add overall_likes varchar(45) null after overall_rating;
alter table experts drop overall_likes;
alter table service_providers add overall_dislikes varchar(45) null after overall_likes;
alter table experts drop overall_dislikes;
alter table experts add user_id varchar(45) null after id;
alter table provider_services add rating float(10,2)  default 0.01 after duration;

--- Redo this shiet IT IS NOT WORKING
alter table service_providers add overall_likes int(11) default 0 after overall_rating ;
alter table service_providers add overall_dislikes int(11) default 0 after overall_rating ;
alter table experts drop business_description;
rename table experts to user_personal_details;
alter table user_personal_details modify user_id int(11) not null;
alter table service_providers add user_id int(10) not null after id;
alter table service_providers modify user_id int(10) unsigned not null after id;
alter table user_personal_details modify user_id int(10) unsigned not null after id;
alter table user_personal_details add constraint `user_user_personal_details_fk-1`  foreign key (user_id) references users(id) ;
alter table service_providers add constraint `user_service_providers_fk-1`  foreign key (user_id) references users(id) ;
alter table user_personal_details drop foreign key experts_service_providers_fk;
alter table user_personal_details drop key experts_service_providers_fk;
alter table user_personal_details drop service_provider_id;
alter table user_personal_details modify passport_photo varchar(200) null;
alter table user_personal_details modify passport_photo json null comment 'sample {"media_url":"...", "media_type":"[video|audio|image]", "size":"xMB"}';
alter table reviews change provicer_service_id provider_service_id int(10) not null ;
alter table reviews add constraint review_user_id_fk_1 foreign key (user_id) references users(id);
alter table reviews add constraint review_service_provider_id_fk_1 foreign key (service_provider_id) references service_providers(id);
alter table reviews modify provider_service_id int(10) unsigned not null ;
alter table reviews add constraint review_provider_services_id_fk_1 foreign key (provider_service_id) references provider_services(id);
 alter table portfolios add constraint portfolios_service_provider_id_fk_1 foreign key (service_provider_id) references service_providers(id);
alter table provider_services modify cost float(10, 2) not null;
alter table provider_services modify duration int(10) not null comment 'Duration in minutes';
alter table provider_services modify description varchar(600) not null;
alter table provider_services add constraint provicder_services_service_id_fk_k foreign key(service_id) references services(id);
alter table provider_services add constraint provicder_services_service_provider_id_fk_k foreign key(service_provider_id) references service_providers(id);
alter table operating_hours add constraint operating_hours_service_provider_id_fk1 foreign key(service_provider_id) references service_providers(id);
alter table provider_services add status_id int(10) null default 1;
alter table operating_hours add status_id int(10) null default 1;
alter table bookings add constraint booking_user_id_fk1 foreign key(user_id) references users(id);
alter table bookings add constraint booking_service_provider_id_fk1 foreign key(service_provider_id) references service_providers(id);
alter table bookings add constraint booking_provider_service_id_fk1 foreign key(provider_service_id) references provider_services(id);
alter table bookings modify booking_duration int(10) not null;
-- Valientine 14th Feb, 2019
alter table booking_trails modify booking_id int(10) unsigned not null;
alter table booking_trails add constraint booking_trails_bookings_id_fk1 foreign key(booking_id) references bookings(id);
alter table booking_trails modify description varchar(200) not null;

alter table bookings drop foreign key booking_users_fk;
alter table bookings drop foreign key bookings_provider_services_fk;
alter table bookings drop foreign key bookings_service_providers_fk;
alter table bookings add booking_type enum('USER LOCATION', 'PROVIDER LOCATION') not null after status_id;
alter table bookings add location json null comment '{"name":"lukume","lat":32.080,"lng":56.93}';
alter table operating_hours change `day` service_day varchar(15) not null;
alter table booking_trails add originator varchar(45)  not null after description
alter table services add service_meta json null after service_name;
create table top_services like services;
alter table services add service_icon varchar(200) null after service_meta;
alter table top_services drop service_name; alter table top_services drop service_meta; alter table top_services drop service_icon; alter table top_services drop deleted_at;
alter table top_services change category_id service_id int(10) unsigned not null;
alter table top_services add foreign key(service_id) references services(id);
alter table services add priority int(10) default 0 after service_meta;
alter table service_providers add index(created_at);

-- Titus edits
ALTER  TABLE  outboxes MODIFY `msisdn` VARCHAR(12);
alter table service_providers add total_requests int(11) default 23 not null after status_id;
alter table service_providers add cover_photo JSON null COMMENT 'sample {"media_url":"...", "media_type":"[video|audio|image]", "size":"xMB"}' after overall_likes;
alter table bookings add amount double(10,2) not null after booking_type;
alter table cost_parameters add cost_parameter varchar(100) not null after service_id;

alter table service_providers add work_location_city varchar(100) null after service_provider_name;
alter table service_providers add business_phone varchar(100) null after service_provider_name;
alter table service_providers add business_email varchar(100) null after service_provider_name;
alter table service_providers add facebook varchar(244) null after service_provider_name;
alter table service_providers add twitter varchar(244) null after service_provider_name;
alter table service_providers add instagram varchar(244) null after service_provider_name;

alter table outboxes modify network enum('SAFARICOM','AIRTEL','TELKOM','EQUITEL','ORANGE','JTL', 'EMAIL') null;
alter table outboxes modify message text not null;
alter table outboxes add email varchar(256)  null;
--alter table users modify phone_no varchar(25) null;
--alter table users modify email varchar(256) null;
alter table outboxes modify msisdn int(15) null;
alter table users add phone_verified int(1) default 0 after remember_token;


