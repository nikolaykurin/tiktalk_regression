library(nlme)

setwd("/tmp")

data <- read.table("results.data", sep=",", head=TRUE)

reg <- nls(treatment_duration ~ patient_age*a + patient_gender*b + treatment_complexity*c + treatment_phases_count*d, data=data, start=c(a=0,b=0,c=0,d=0))

save(reg, file = "nonlinear_regression_model.rda")
