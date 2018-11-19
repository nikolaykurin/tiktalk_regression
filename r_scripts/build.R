library(nlme)

setwd("/tmp")

data <- read.table("results.data", sep=",", head=TRUE)

data <- na.omit(data)
data <- data[order(data$treatment_duration),]

ind <- sample(2, nrow(data), replace=TRUE, prob=c(0.9, 0.1))
train <- data[ind==1,]

reg <- nls(treatment_duration ~ patient_age*a + patient_gender*b + treatment_complexity*c, data=train, start=c(a=0,b=0,c=0))

save(reg, file = "nonlinear_regression_model.rda")
