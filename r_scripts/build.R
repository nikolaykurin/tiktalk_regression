library(nlme)

setwd("/tmp")

data <- read.table("results.data", sep=",", head=TRUE)

reg <- nls(treatment_duration ~ patient_age*a + treatment_complexity*b + treatment_phases_count*c, data=data, start=c(a=0,b=0,c=0))

save(reg, file = "nonlinear_regression_model.rda")
