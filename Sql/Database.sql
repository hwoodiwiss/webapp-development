CREATE TABLE UserAccessLevels (
	Id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(50) NOT NULL
    );

INSERT INTO UserAccessLevels
VALUES (1, 'User'), (2, 'Admin');

CREATE TABLE Users (
  Id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  Email VARCHAR(255) NOT NULL,
  PassHash VARCHAR(128) NOT NULL,
  PassSalt VARCHAR(128) NOT NULL,
  FirstName VARCHAR(255) NOT NULL,
  LastName VARCHAR(255) NOT NULL,
  JobTitle VARCHAR(255) NOT NULL,
  AccessLevelId INT NOT NULL,
  Timestamp DATETIME NOT NULL,
  Active BIT NOT NULL DEFAULT 1,
  CONSTRAINT User_AccessLevel FOREIGN KEY(AccessLevelId) REFERENCES UserAccessLevels(Id)
);

CREATE TABLE Courses (
	Id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    StartDate DATE NOT NULL,
    Duration INT NOT NULL,
    Capacity INT NOT NULL,
    Active BIT NOT NULL DEFAULT 1,
    Description VARCHAR(255) NOT NULL
);

CREATE TABLE Bookings (
	Id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    UserId INT NOT NULL,
    CourseId INT NOT NULL,
    Timestamp DATETIME NOT NULL,
    CONSTRAINT Booking_User FOREIGN KEY(UserId) REFERENCES Users(Id),
    CONSTRAINT Booking_Course FOREIGN KEY(CourseId) REFERENCES Courses(Id)
);
