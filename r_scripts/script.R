library(nlme)

setwd("/tmp")

data <- read.table("results.data", sep=",", head=TRUE) 
head(data)
plot(data, pch=19)

data <- na.omit(data)
data <- data[order(data$treatment_duration),]

ind <- sample(2, nrow(data), replace=TRUE, prob=c(0.9, 0.1))
train <- data[ind==1,]
test <- data[ind==2,]

reg <- nls(treatment_duration ~ patient_age*a + treatment_complexity*b + treatment_phases_count*c, data=data, start=c(a=0,b=0,c=0))

print(reg)

pred <- predict(reg, test)


alldata <- rbind(data.frame(train, color=4), data.frame(test, color=2))
plot(alldata$treatment_duration ~ alldata$treatment_complexity, col=alldata$color, pch=19)
lines(train$treatment_complexity, fitted(reg), col="green", lwd=2)
segments(test$treatment_complexity, test$treatment_duration, test$treatment_complexity, pred, col="black")

print(data.frame(test, treatment_duration_predicted=pred))
err2 = sqrt(sum((test$treatment_duration - pred)^2))/length(pred)
print(err2)

newdata <- data.frame(patient_age=c(12,8,16,9),treatment_complexity=c(1,2,2,2),treatment_phases_count=c(1,5,2,1))
newdata$treatment_duration <- predict(reg, newdata)
print(newdata)
