
USE clinicapro;
INSERT INTO users VALUES (1,'Administrador','admin@local',SHA2('123456',256),'staff'),(2,'Juliana Silva','paciente@local',SHA2('123456',256),'patient');
INSERT INTO patients VALUES (1,2,'1990-05-10','F',165);
INSERT INTO weight_history VALUES
(1,1,85,'2023-10-31'),(2,1,84.5,'2023-11-07'),(3,1,84,'2023-11-14'),(4,1,83,'2023-11-21'),
(5,1,82,'2023-11-30'),(6,1,81,'2023-12-07'),(7,1,80,'2023-12-14'),(8,1,79,'2023-12-21'),
(9,1,78,'2023-12-31'),(10,1,77,'2024-01-07'),(11,1,76,'2024-01-14'),(12,1,75,'2024-01-21');
INSERT INTO bioimpedance VALUES (1,1,24.5,52.1,7,58.2,1650,'2024-01-21');
INSERT INTO monjaro_applications VALUES
(1,1,7.5,'2024-01-21'),(2,1,7.5,'2024-01-14'),(3,1,7.5,'2024-01-07'),(4,1,7.5,'2023-12-31');
INSERT INTO procedures VALUES (1,'Aplicação Monjaro',500),(2,'Sessão EndoLazer',800);
INSERT INTO appointments VALUES
(1,1,1,'2024-05-19 14:00:00','CONFIRMADO','Cartão'),
(2,1,2,'2024-05-24 10:30:00','AGENDADO','PIX');
