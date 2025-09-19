import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
import time
def test_browse():
    driver=webdriver.Chrome()
    driver.maximize_window()  
    driver.get("http://127.0.0.1:8000/") 
    
    browse_btn=driver.find_element(By.XPATH,"//label[normalize-space()='Browse']")
    browse_btn.click()
    time.sleep(4)
